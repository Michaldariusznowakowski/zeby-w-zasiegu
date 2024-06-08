@extends('partials.main.email')
@section('content')
    <h1>Witaj {{ $user->name }} {{ $user->surname }}!</h1>
    <p> Twoja wizyta u {{ $doctor->name }} {{ $doctor->surname }} została przeniesiona i potwierdzona. </p>
    <p> Data wizyty: {{ $date }} </p>
    <p> Godzina wizyty: {{ $time }} </p>
    <p> Opis wizyty: {{ $description }} </p>
    <p> Proszę przybyć na wizytę punktualnie. </p>
    <p> Pozdrawiamy, </p>
    <p> Zespół Zęby w Zasięgu </p>
    <p> Wiadomość wygenerowana automatycznie, prosimy na nią nie odpowiadać. </p>
@endsection
