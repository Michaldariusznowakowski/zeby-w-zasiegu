@extends('partials.main.email')
@section('content')
    <h1>Witaj {{ $user->name }} {{ $user->surname }}!</h1>
    <p> Twój adres e-mail został potwierdzony. </p>
    <p> Pozdrawiamy, </p>
    <p> Zespół Zęby w Zasięgu </p>
    <p> Wiadomość wygenerowana automatycznie, prosimy na nią nie odpowiadać. </p>
@endsection
