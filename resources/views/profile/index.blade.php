@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Profil użytkownika')
{{-- @section('api_token', true) --}}
@section('content')
    <div class="container">
        <h1>Cześć, {{ Auth::user()->name }}</h1>
        <p> Witaj w swoim profilu użytkownika. Możesz tutaj edytować swoje dane. </p>
        <p>Twoje dane:</p>
        <form method="POST" action="{{ route('profile.edit') }}">
            @csrf
            <label for="name">Imię:</label>
            <input type="text" id="name" name="name" value="{{ Auth::user()->name }}" required>
            <label for="surname">Nazwisko:</label>
            <input type="text" id="surname" name="surname" value="{{ Auth::user()->surname }}" required>
            <label for="phone">Telefon:</label>
            <input type="tel" id="phone" name="phone_number" value="{{ Auth::user()->phone_number }}" required>
            {{-- <label for="marketing">Chcę otrzymywać informacje marketingowe:</label>
            <input type="checkbox" id="marketing" name="marketing" value="true"> --}}
            <button id="submit">Zapisz zmiany</button>
        </form>
        <hr>
        <h2>Inne ustawienia</h2>
        <div class="grid">
            <a role="button" href="{{ route('login.changepasswordForm') }}">Zmień hasło</a>
            {{-- <a role="button" href="#">Zmień e-mail</a> --}}
            {{-- <a role="button" class="button-red" href="#">Usuń konto</a> --}}
        </div>
        <hr>
        <h2>Nie znalazłeś tego, czego szukasz?</h2>
        <p>Skontaktuj się z nami, a my postaramy się pomóc.</p>
        <a role="button" href="mailto:zebywzasiegu@gmail.com">Napisz do nas</a>
        <hr>
    </div>

@endsection
@vite('resources/js/search.ts')
