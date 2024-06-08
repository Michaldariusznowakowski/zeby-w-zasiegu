@extends('partials.main.email')
@section('content')
    <h1>Witaj {{ $user->name }} {{ $user->surname }}!</h1>
    <p> Masz {{ $unreadedMessagesCount }} nowych wiadomości. </p>
    <p> Aby je przeczytać, kliknij w poniższy link: </p>
    <a href="{{ route('chat') }}">Przeczytaj wiadomości</a>
    <p> Pozdrawiamy, </p>
    <p> Zespół Zęby w Zasięgu </p>
    <p> Wiadomość wygenerowana automatycznie, prosimy na nią nie odpowiadać. </p>
@endsection
