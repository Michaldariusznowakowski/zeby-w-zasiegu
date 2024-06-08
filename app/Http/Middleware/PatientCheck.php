<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PatientCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->active == false || auth()->user()->email_verified_at == null) {
            logger()->critical('User is not active and tried to access patient panel. User email: ' . auth()->user()->email);
            auth()->logout();
            return redirect()->route('home')->with('error', 'Konto nie zostało aktywowane, sprawdź email w celu weryfikacji konta');
        }
        if (auth()->user()->public_key == null || auth()->user()->signed_email == null) {
            return redirect()->route('login.encrypt')->with('error', 'Klucz publiczny nie został zapisany. Jeżeli błąd się powtarza skontaktuj się z administratorem');
        }
        if (auth()->user()->role == 'patient') {
            return $next($request);
        }
        return redirect()->route('home')->with('error', 'Nie masz uprawnień do wykonania tej operacji');
    }
}
