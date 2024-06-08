@extends('partials.main.email')
@section('content')
    <h1>Witaj {{ $user->name }} {{ $user->surname }}!</h1>
    <p> Pacjent {{ $patient->name }} {{ $patient->surname }} zarezerwował wizytę u Ciebie. </p>
    <p> Data wizyty: {{ $date }} </p>
    <p> Godzina wizyty: {{ $time }} </p>
    <p> Opis wizyty: {{ $description }} </p>
    <p> Proszę potwierdzić wizytę w systemie, lub skontaktuj się z pacjentem w celu zmiany terminu. </p>
    <p> Pozdrawiamy, </p>
    <p> Zespół Zęby w Zasięgu </p>
    <p> Wiadomość wygenerowana automatycznie, prosimy na nią nie odpowiadać. </p>
@endsection
