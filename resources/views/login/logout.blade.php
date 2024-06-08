@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Logowanie')
@section('content')
    <div class="container">
        <p> Zostałeś poprawnie wylogowany </p>
        <p> Przenoszenie do strony głównej </p>
    </div>
@vite('resources/js/logout.ts')
@endsection
