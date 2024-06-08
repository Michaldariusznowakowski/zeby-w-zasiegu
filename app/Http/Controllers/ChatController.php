<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Chatroom;
use App\Models\Offer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Events\NewMessage;

class ChatController extends Controller
{
    public function index()
    {
        $chatrooms = null;
        if (Auth::user()->role === 'dentist') {
            $chatrooms = Chatroom::where('dentist_id', Auth::user()->id)->orderBy('updated_at', 'desc')->get();
        } else {
            $chatrooms = Chatroom::where('patient_id', Auth::user()->id)->orderBy('updated_at', 'desc')->get();
        }
        $recipients = [];
        foreach ($chatrooms as $chatroom) {
            $role = Auth::user()->role;
            $reciver_id = null;
            $offer = null;
            $has_unread_messages = null;
            if ($role != 'patient') {
                $reciver_id = $chatroom->patient_id;
                $has_unread_messages = $chatroom->patient_has_unread_messages;
                $offer = Offer::where('doctor_id', $chatroom->dentist_id)->first();
            } else {
                $reciver_id = $chatroom->dentist_id;
                $has_unread_messages = $chatroom->dentist_has_unread_messages;
            }
            $reciver_name = User::where('id', $reciver_id)->first()->name;
            $reciver_surname = User::where('id', $reciver_id)->first()->surname;
            $last_msg = Message::where('chatroom_id', $chatroom->id)->orderBy('sent_at', 'desc')->first();
            if ($last_msg === null) {
                $date_of_last_message = null;
            } else {
                $date_of_last_message = $last_msg->sent_at;
            }
            if ($offer !== null) {
                $offer_link = route('offers.show', ['id' => $offer->id]);
                $offer_photo = $offer->image;
            } else {
                $offer_link = null;
                $offer_photo = null;
            }
            $recipients[] = [
                'name' => $reciver_name,
                'surname' => $reciver_surname,
                'chatroom_id' => $chatroom->id,
                'offer_photo' => $offer_photo,
                'offer_link' => $offer_link,
                'has_unread_messages' => $has_unread_messages,
                'date_of_last_message' => $date_of_last_message
            ];
        }
        return view('chat.index', ['recipients' => $recipients, 'pagination' => $chatrooms]);
    }
    public function chatWith(Request $request)
    {
        validator($request->route()->parameters(), [
            'chatroom_id' => 'required|integer|exists:chatrooms,id'
        ])->validate();
        $chatroom_id = $request->route()->parameters()['chatroom_id'];
        $chatroom = Chatroom::where('id', $chatroom_id)->first();
        $dentist = User::where('id', $chatroom->dentist_id)->first();
        $offer = Offer::where('dentist_id', $dentist->id)->first();
        if (Auth::user()->role === 'dentist') {
            $recipient_id = $chatroom->patient_id;
            $sender_id = $chatroom->dentist_id;
        } else {
            $recipient_id = $chatroom->dentist_id;
            $sender_id = $chatroom->patient_id;
        }
        $recipient = User::where('id', $recipient_id)->first();
        $reciver_public_key = $recipient->public_key;

        $offer_link = null;
        $offer_photo = null;
        if ($offer !== null) {
            $offer_link = route('offers.show', ['id' => $offer->id]);
            $offer_photo = $offer->image;
        }
        $messages = Message::where('chatroom_id', $chatroom_id)->orderBy(
            'sent_at',
            'desc'
        )->get();
        return view(
            'chat.chatroom',
            [
                'messages' => $messages,
                'chatroom_id' => $chatroom_id,
                'recipient' => $recipient,
                'sender_id' => $sender_id,
                'recipient_id' => $recipient_id,
                'reciver_public_key' => $reciver_public_key,
                'offer_link' => $offer_link,
                'offer_photo' => $offer_photo
            ]
        );
    }

    public function sendMessage(Request $request)
    {
        $decoded = json_decode($request->getContent(), true);
        if ($decoded === null) {
            return new Response('Data is not valid', 400);
        }
        $validator = Validator::make($decoded, [
            'chatroom_id' => 'required|integer',
            'recipient_id' => 'required|integer',
            'message' => 'required|string',
            'iv' => 'required|string',
            'ek' => 'required|string',
            'ekl' => 'required|string',
            'type' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return new Response('Data is not valid', 400);
        }
        $chatroom = Chatroom::where('id', $decoded['chatroom_id'])->first();
        if ($chatroom === null) {
            return new Response('Chatroom not found', 404);
        }
        if (Auth::user()->role === 'dentist' && $chatroom->dentist_id !== Auth::user()->id) {
            return new Response('Unauthorized', 401);
        }
        if (Auth::user()->role === 'patient' && $chatroom->patient_id !== Auth::user()->id) {
            return new Response('Unauthorized', 401);
        }
        $message = new Message();
        $message->chatroom_id = $chatroom->id;
        $message->message = $decoded['message'];
        $message->iv = $decoded['iv'];
        $message->ek = $decoded['ek'];
        $message->ekl = $decoded['ekl'];
        $message->type = 0;
        $message->sender_id = Auth::user()->id;
        $message->recipient_id = $decoded['recipient_id'];
        $message->save();
        if ($message->recipient_id === $chatroom->dentist_id) {
            $chatroom->dentist_has_unread_messages = true;
            $chatroom->sent_email_to_dentist = false;
        } else {
            $chatroom->patient_has_unread_messages = true;
            $chatroom->sent_email_to_patient = false;
        }
        $chatroom->save();
        Broadcast(new NewMessage($message))->toOthers();
        return new Response('Message sent', 200);
    }

    public function startNewChat(Request $request)
    {
        validator($request->route()->parameters(), [
            'user_id' => 'required|integer|exists:users,id'
        ])->validate();
        if (Auth::user()->role === 'dentist') {
            $dentist_id = Auth::user()->id;
            $patient_id = $request->route()->parameters()['user_id'];
        } else {
            $dentist_id = $request->route()->parameters()['user_id'];
            $patient_id = Auth::user()->id;
        }
        $chatroom = Chatroom::where('patient_id', $patient_id)->where('dentist_id', $dentist_id)->first();
        if ($chatroom === null) {
            $chatroom = new Chatroom();
            $chatroom->patient_id = $patient_id;
            $chatroom->dentist_id = $dentist_id;
            $chatroom->save();
        }
        return redirect()->route('chat.with', ['chatroom_id' => $chatroom->id]);
    }

    public function sendFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|string',
            'message' => 'required|json'
        ]);
        $decoded = json_decode($request->message, true);
        if ($decoded === null) {
            return new Response('Data is not valid', 400);
        }
        $validator = Validator::make($decoded, [
            'chatroom_id' => 'required|integer',
            'recipient_id' => 'required|integer',
            'message' => 'required|string',
            'iv' => 'required|string',
            'ek' => 'required|string',
            'ekl' => 'required|string',
            'type' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return new Response('Data is not valid', 400);
        }
        $chatroom = Chatroom::where('id', $decoded['chatroom_id'])->first();
        if ($chatroom === null) {
            return new Response('Chatroom not found', 404);
        }
        if (Auth::user()->role === 'dentist' && $chatroom->dentist_id !== Auth::user()->id) {
            return new Response('Unauthorized', 401);
        }
        if (Auth::user()->role === 'patient' && $chatroom->patient_id !== Auth::user()->id) {
            return new Response('Unauthorized', 401);
        }
        $message = new Message();
        $message->chatroom_id = $chatroom->id;
        $message->type = $decoded['type'];
        $message->sender_id = Auth::user()->id;
        $message->iv = $decoded['iv'];
        $message->ek = $decoded['ek'];
        $message->ekl = $decoded['ekl'];
        $message->recipient_id = $decoded['recipient_id'];
        $file = $request->file;
        $file_name = Date('Y_m_d_H_i_s') . '_' . $decoded['message'];
        Storage::disk('local')->put('private/' . 'chat_files/' . 'chatroom_' . $chatroom->id . '/' . $file_name, $file);
        $message->message = $file_name;
        $message->save();
        if (Auth::user()->role === 'dentist') {
            $chatroom->patient_has_unread_messages = true;
            $chatroom->sent_email_to_patient = false;
        } else {
            $chatroom->dentist_has_unread_messages = true;
            $chatroom->sent_email_to_dentist = false;
        }
        $chatroom->save();
        Broadcast(new NewMessage($message))->toOthers();
        return new Response('File sent', 200);
    }

    public function downloadFile(Request $request)
    {
        $validator = Validator::make($request->route()->parameters(), [
            'message_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return new Response('Data is not valid', 400);
        }
        $message = Message::where('id', $request->message_id)->first();
        if ($message === null) {
            return new Response('Message not found', 404);
        }
        if (Auth::user()->role === 'dentist' && $message->sender_id !== Auth::user()->id) {
            return new Response('Unauthorized', 401);
        }
        if (Auth::user()->role === 'patient' && $message->sender_id !== Auth::user()->id) {
            return new Response('Unauthorized', 401);
        }
        $chatroom = Chatroom::where('id', $message->chatroom_id)->first();
        $file_path = 'private/' . 'chat_files/' . 'chatroom_' . $chatroom->id . '/' . $message->message;
        $file_name = $message->message;

        $file_size = round(Storage::disk('local')->size($file_path) / 1048576, 4);

        return view('chat.download', ['file_path' => $file_path, 'file_name' => $file_name, 'file_size' => $file_size, 'message' => $message]);
    }

    public function setSeenToAllBefore(Request $request)
    {
        $json = json_decode($request->getContent(), true);
        $validator = Validator::make($json, [
            'message_id' => 'required|integer',
            'chatroom_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return new Response('Data is not valid', 400);
        }
        $chatroom = Chatroom::where('id', $json['chatroom_id'])->first();
        if ($chatroom === null) {
            return new Response('Chatroom not found', 404);
        }
        if (Auth::user()->id !== $chatroom->dentist_id && Auth::user()->id !== $chatroom->patient_id) {
            return new Response('Unauthorized', 401);
        }
        $message = Message::where('id', $json['message_id'])->first();
        if ($message === null) {
            return new Response('Message not found', 404);
        }
        if ($message->recipient_id !== Auth::user()->id) {
            return new Response('Unauthorized', 401);
        }
        $message->recipient_read = true;
        $message->save();
        $messages = Message::where('chatroom_id', $chatroom->id)->whereDate('sent_at', '<', $message->sent_at)->where('recipient_id', Auth::user()->id)->where('recipient_read', false)->get();
        foreach ($messages as $msg) {
            $msg->recipient_read = true;
            $msg->save();
        }
        $unreadedMessagesCount = Message::where('chatroom_id', $chatroom->id)->where('recipient_read', false)->where('recipient_id', Auth::user()->id)->count();
        if ($unreadedMessagesCount === 0) {
            if ($message->recipient_id === $chatroom->dentist_id) {
                $chatroom->dentist_has_unread_messages = false;
                $chatroom->sent_email_to_dentist = false;
            } else {
                $chatroom->patient_has_unread_messages = false;
                $chatroom->sent_email_to_patient = false;
            }
            $chatroom->save();
        }
        return new Response('Messages seen', 200);
    }
}
