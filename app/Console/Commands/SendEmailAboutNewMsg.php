<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Chatroom;
use App\Models\User;
use App\Mail\NewMessage;
use Illuminate\Support\Facades\Mail;

class SendEmailAboutNewMsg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-email-about-new-msg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if there are any new messages and send an email to the user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for new messages');
        $chatrooms = Chatroom::where('sent_email_to_patient', false)->orWhere('sent_email_to_dentist', false)->get();
        $users = [];
        foreach ($chatrooms as $chatroom) {
            if ($chatroom->dentist_has_unread_messages && !$chatroom->sent_email_to_dentist) {
                $users[$chatroom->dentist_id] = User::find($chatroom->dentist_id);
            }
            if ($chatroom->patient_has_unread_messages && !$chatroom->sent_email_to_patient) {
                $users[$chatroom->patient_id] = User::find($chatroom->patient_id);
            }
        }
        $unreadedMessagesCount = [];
        foreach ($users as $user) {
            $unreadedMessagesCount[$user->id] = Chatroom::where('dentist_id', $user->id)->where('dentist_has_unread_messages', true)->count() + Chatroom::where('patient_id', $user->id)->where('patient_has_unread_messages', true)->count();
        }
        $this->info('Sending emails to ' . count($users) . ' users');
        foreach ($users as $user) {
            Mail::to($user->email)->send(new NewMessage($user, $unreadedMessagesCount[$user->id]));
            $chatrooms = Chatroom::where('dentist_id', $user->id)->orWhere('patient_id', $user->id)->get();
            foreach ($chatrooms as $chatroom) {
                if ($chatroom->dentist_id == $user->id) {
                    $chatroom->sent_email_to_dentist = true;
                } else {
                    $chatroom->sent_email_to_patient = true;
                }
                $chatroom->save();
            }
        }
        $this->info('Emails sent successfully');
    }
}
