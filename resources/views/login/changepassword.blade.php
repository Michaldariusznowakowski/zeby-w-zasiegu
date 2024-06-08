@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Zmiana hasła')
@section('content')
    <div class="container">
        <form method="POST" action="{{ route('login.updatepassword') }}">
            @csrf
            <h1> Zmiana hasła </h1>
            <label for="password">Hasło</label>
            <input type="password" name="password" id="password" placeholder="Hasło" />
            <label for="password_confirmation">Powtórz hasło</label>
            <x-errorinput name="password" />
            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Powtórz hasło" />
            <x-errorinput name="password" />
            <input type="submit" value="Zmień hasło" class="button" />
        </form>
    </div>
    </div>
@endsection
