@extends('partials.main.email')
@section('content')
    <h1>Witaj {{ $user->name }} {{ $user->surname }}!</h1>
    <p> Dziękujemy za rejestrację w serwisie Zęby w Zasięgu. </p>
    <p> Aby dokończyć rejestrację, kliknij w poniższy link: </p>
    <a href="{{ route('email_verification', ['token' => $token, 'email' => $user->email]) }}">Potwierdź
        e-mail</a>
    <p> Jeśli nie rejestrowałeś się w serwisie Zęby w Zasięgu, zignoruj tę wiadomość. </p>
    <p> Pozdrawiamy, </p>
    <p> Zespół Zęby w Zasięgu </p>
    <p> Wiadomość wygenerowana automatycznie, prosimy na nią nie odpowiadać. </p>
@endsection
