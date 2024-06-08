<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Personal_access_token;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\EmailController;
use App\Models\Offer;
use App\Models\Event;
use App\Models\Chatroom;
use Illuminate\Http\Response;


class LoginController extends Controller
{
    public function index()
    {
        return view('login.login');
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user->email_verified_at == null || $user->active == false) {
            return redirect()->route('login')->with('error', 'Konto nie zostało aktywowane, sprawdź email w celu weryfikacji konta');
        }
        if ($user->role == 'dentist' && $user->verified == false) {
            return redirect()->route('login')->with('error', 'Konto dentysty nie zostało zweryfikowane, sprawdź email w celu weryfikacji konta');
        }
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $id = Auth::id();
            $public_key = Auth::user()->public_key;
            if ($public_key == null) {
                return redirect()->route('login.encrypt');
            } else {
                return redirect()->route('login.decrypt');
            }
        }
        return redirect()->route('login')->with('error', 'Nieprawidłowe hasło');
    }
    public function logout()
    {
        Auth::logout();
        return view('login.logout');
    }
    public function register()
    {
        return view('login.register');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'surname' => 'required',
            'telephone' => 'required|numeric',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed|min:8|max:255'
        ]);
        $role = null;
        DB::beginTransaction();
        if ($request->dentistCheckbox) {
            $role = 'dentist';
        } else {
            $role = 'patient';
        }
        $user = User::create([
            'email' => $request->email,
            'name' => $request->name,
            'surname' => $request->surname,
            'phone_number' => $request->telephone,
            'role' => $role,
            'active' => false,
            'password' => bcrypt($request->password),
            'private_key' => null,
            'public_key' => null
        ]);
        $id = $user->id;
        if ($role == 'dentist') {
            $offer = Offer::create([
                'doctor_id' => $user->id,
                'active' => false,
                'description' => null,
                'image' => 'default/profile.png',
                'address' => null,
                'longitude' => null,
                'latitude' => null
            ]);
            $offer->save();
        }
        if ($this->generateTokenRegister($id) == false) {
            DB::rollback();
            return redirect()->back()->with('error', 'Nie udało się wygenerować tokenu weryfikacyjnego, skontaktuj się z administratorem strony.');
        }
        $token = Personal_access_token::where('user_id', $user->id)->first()->token;
        if (EmailController::sendEmailRegisterVerification($user, $token) == false) {
            DB::rollback();
            return redirect()->back()->with('error', 'Nie udało się wysłać emaila weryfikacyjnego, skontaktuj się z administratorem strony.');
        }
        DB::commit();
        return redirect()->route('login')->with('success', 'Konto zostało utworzone, sprawdź email w celu weryfikacji konta');
    }
    private function generateTokenRegister($user_id)
    {
        $expires_at = time() + 86400; // 24 hours
        $random_token_64 = bin2hex(random_bytes(32));
        $token = Personal_access_token::create([
            'user_id' => $user_id,
            'name' => 'emailVerification',
            'token' =>  $random_token_64,
            'last_used_at' => null,
            'expires_at' => $expires_at
        ]);
        if ($token) {
            return true;
        }
    }

    public function verifyEmail($token)
    {
        $token = Personal_access_token::where('token', $token)->first();
        if ($token) {
            $user_id = $token->user_id;
            $user = User::where('id', $user_id)->first();
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->save();
            $token->delete();
            return redirect()->route('login');
        }
        return redirect()->route('login');
    }
    public function emailVerification(Request $request)
    {
        $request->merge(['token' => $request->token, 'email' => $request->email]);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
            'token' => 'required|exists:personal_access_tokens'
        ]);
        if ($validator->fails()) {
            return redirect()->route('register')->with('error', 'Token wygasł, lub został już użyty. Jeżeli nie dostałeś potwierdzenia aktywacji konta, utwórz nowe konto. Przepraszamy
            za utrudnienia.');
        }
        $email = $request->email;
        $user = User::where('email', $email)->first();
        $token = Personal_access_token::where('token', $request->token)
            ->where('name', 'emailVerification')
            ->where('user_id', $user->id)
            ->first();
        if ($token->expires_at < time()) {
            $user->delete();
            $token->delete();
            return redirect()->route('login')->with('error', 'Token wygasł, utwórz nowe konto. Przepraszamy za utrudnienia.');
        }
        $user->email_verified_at = date('Y-m-d H:i:s');
        $token->delete();
        $user->active = true;
        $user->save();
        EmailController::sendEmailConfirmVerification($user);
        return redirect()->route('login')->with('success', 'Konto zostało aktywowane, możesz się zalogować.');
    }
    public function home()
    {
        $num_of_visits = 0;
        $num_of_messages = 0;
        $user = Auth::user();
        if ($user->role == 'dentist') {
            $num_of_visits = Event::where('doctor_id', $user->id)->where('cancelled', false)->whereDate('start_date', '>=', date('Y-m-d'))->count();
            $num_of_messages = Chatroom::where('doctor_id', $user->id)->where('dentist_has_unread_messages', true)->count();
        } else {
            $num_of_visits = Event::where('patient_id', $user->id)->where('cancelled', false)->whereDate('start_date', '>=', date('Y-m-d'))->count();
            $num_of_messages = Chatroom::where('patient_id', $user->id)->where('patient_has_unread_messages', true)->count();
        }
        return view('home', ['num_of_visits' => $num_of_visits, 'num_of_messages' => $num_of_messages]);
    }
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'password_confirmation' => 'required|min:8|max:255'
        ]);
        $user = User::where('id', Auth::id())->first();
        if ($request->password != $request->password_confirmation) {
            return back()->with('error', 'Hasła nie są takie same');
        }
        $user->password = bcrypt($request->password);
        $user->save();
        return redirect()->route('profile')->with('success', 'Hasło zostało zmienione');
    }
    public function changePasswordForm()
    {
        return view('login.changepassword');
    }
    public function encrypt()
    {
        if (Auth::check() && Auth::user()->public_key == null && Auth::user()->signed_email == null) {
            return view('login.encrypt');
        } else {
            return redirect()->route('login.decrypt');
        }
    }
    public function storeKeys(Request $request)
    {
        if (!Auth::check()) {
            return new Response('Brak dostępu', 403);
        }
        $content = json_decode($request->getContent(), true);
        if ($content === null) {
            return new Response('Nieprawidłowe dane content error', 400);
        }
        $validator = Validator::make($content, [
            'signed_email' => 'required | string',
            'public_key' => 'required | string'
        ]);
        if ($validator->fails()) {
            return new Response('Nieprawidłowe dane', 400);
        }
        $user = User::find(Auth::id());
        if ($user->public_key != null || $user->signed_email != null) {
            return new Response('Klucz publiczny został już zapisany', 400);
        }
        $user->signed_email = $content['signed_email'];
        $user->public_key = $content['public_key'];
        $user->save();
        return new Response('Klucz publiczny został zapisany', 200);
    }
    public function decrypt()
    {
        if (Auth::check() && Auth::user()->public_key != null && Auth::user()->signed_email != null) {
            return view('login.decrypt');
        }
        if (Auth::check()) {
            return redirect()->route('login.encrypt')->with('error', 'Klucz publiczny nie został zapisany. Jeżeli błąd się powtarza skontaktuj się z administratorem');
        }
    }
    public function purgeKeys()
    {
        if (Auth::check()) {
            $user = User::find(Auth::id());
            $user->public_key = null;
            $user->signed_email = null;
            $user->save();
            DB::table('messages')->where('sender_id', Auth::id())->update(['sender_lost_key' => true]);
            DB::table('messages')->where('recipient_id', Auth::id())->update(['recipient_lost_key' => true]);
            return redirect()->route('login.encrypt')->with('success', 'Klucze zostały usunięte');
        }
    }
    public function getSignedEmail()
    {
        if (Auth::check()) {
            $user = User::find(Auth::id());
            $data = $user->signed_email;
            return response($data, 200)->header('Content-Type', 'application/text');
        }
    }
    public function getEmail()
    {
        if (Auth::check()) {
            $user = User::find(Auth::id());
            return response($user->email, 200)->header('Content-Type', 'application/text');
        }
        return new Response('Brak dostępu', 403);
    }
    public function getPublicKeyAny(Request $request)
    {
        $decoded = json_decode($request->getContent(), true);
        if ($decoded === null) {
            return new Response('Data is not valid', 400);
        }
        $validator = Validator::make($decoded, [
            'user_id' => 'required | integer'
        ]);
        if ($validator->fails()) {
            return new Response('Data is not valid', 400);
        }
        $user = User::where('id', $decoded['user_id'])->first();
        if ($user === null) {
            return new Response('Data is not valid', 404);
        }
        if ($user->public_key === null) {
            return new Response('User does not have a public key', 404);
        }
        $data = $user->public_key;
        return response($data, 200)->header('Content-Type', 'application/text');
    }
}
