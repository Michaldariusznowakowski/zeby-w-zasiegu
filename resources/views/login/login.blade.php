@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Logowanie')
@section('content')
    <div class="container">
        <form href="{{ route('login') }}" method="POST">
            @csrf
            <h1>Logowanie</h1>
            <label for="email">Adres e-mail</label>
            <input type="email" name="email" id="email" placeholder="Adres e-mail " value="{{ old('email') }}" />
            <x-errorinput name="email" />
            <label for="password">Hasło</label>
            <input type="password" name="password" id="password" placeholder="Hasło" />
            <x-errorinput name="password" />
            <input type="submit" value="Zaloguj się" class="button" />

        </form>
        <span> Nie masz konta? </span> <br />
        <a role="button" class="secondary" href="{{ route('register') }}">Zarejestruj się</a> <br />
    </div>
@endsection
