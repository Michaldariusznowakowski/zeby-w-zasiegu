@extends('partials.main.email')
@section('content')
    <h1>Witaj {{ $user->name }} {{ $user->surname }}!</h1>
    <p> Wizyta dnia {{ $date }} o godzinie {{ $time }} została odwołana, o więcej informacji prosimy o
        kontakt z lekarzem.
    <p> Przepraszamy za wszelkie niedogodności.</p>
    <p> Pozdrawiamy, </p>
    <p> Zespół Zęby w Zasięgu </p>
    <p> Wiadomość wygenerowana automatycznie, prosimy na nią nie odpowiadać. </p>
@endsection
