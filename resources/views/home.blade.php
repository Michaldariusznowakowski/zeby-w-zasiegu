@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Panel Główny')
@section('content')
    <div class="container">

        <h1>Dzień dobry, {{ Auth::user()->name }}</h1>
        @if (Auth::user()->role == 'dentist' && App\Http\Controllers\OffersController::isActive() != 0)
            <hr>
            <article class="doctor-inactive">
                <h1 class="pico-color-red-500">Twój profil dentysty jest <strong> nieaktywny.</strong>
                </h1>
                <p>Oznacza to, że pacjenci nie mogą umawiać wizyt.<br>
                    Nie można odnaleźć Twojego profilu w wyszukiwarce.<br>
                    Wciaż będziesz mógł zarządzać swoimi wizytami oraz
                    kontaktować się z pacjentami poprzez czat.
                </p>
                <a type="button" href="{{ route('offers.edit') }}">Ustawienia konta → Edytuj profil lekarza</a>
            </article>
            <hr>
        @endif
        <div style="background-image: url('{{ asset('images/hero-start-login.webp') }}')" class="hero-container">
            <div class="hero-text">
                <h1>
                    Witamy w panelu głównym,
                    @if (Auth::user()->role == 'patient')
                        pacjencie.
                        Znajdziesz tutaj informacje o swoich wizytach.
                        Zaplanujesz kolejne wizyty oraz skontaktujesz się z lekarzem.
                        <hr>
                        Z nami zdrowe zęby są w zasięgu Twojej ręki.
                    @else
                        lekarzu.
                        Znajdziesz tutaj informacje o swoich wizytach.
                        Skontaktujesz się z pacjentem oraz zarządzisz swoim kalendarzem.
                        <hr>
                        Z nami zdrowe zęby są w zasięgu Twojej ręki.
                    @endif

                </h1>

            </div>
        </div>
        <h3>Dzisiaj jest {{ date('d.m.Y') }}</h3>
        <hr>
        @if (Auth::user()->role == 'patient')
            @if ($num_of_visits == 0)
                <h3>Nie masz żadnych wizyt w najbliższym czasie.</h3>
                <a role="button" href="{{ route('offers.search') }}">Umów wizytę</a>
            @else
                <h3>Masz {{ $num_of_visits }} wizyt w najbliższym czasie.</h3>
                <a role="button" href="{{ route('appointment.calendar') }}">Zobacz swoje wizyty</a>
                <hr>
            @endif
            @if ($num_of_messages == 0)
                <h3>Nie masz żadnych nowych wiadomości.</h3>
            @else
                <h3>Masz {{ $num_of_messages }} nieprzeczytanych wiadomości.</h3>
                <a role="button" href="{{ route('chat') }}">Przeczytaj wiadomości</a>
            @endif
        @endif
        @if (Auth::user()->role == 'dentist')
            @if ($num_of_visits == 0)
                <h3>Nie masz żadnych wizyt w najbliższym czasie.</h3>
            @else
                <h3>Masz {{ $num_of_visits }} wizyt w najbliższym czasie.</h3>
                <a role="button" href="{{ route('appointment.calendar') }}">Zobacz swoje wizyty</a>
                <hr>
            @endif
            @if ($num_of_messages == 0)
                <h3>Nie masz żadnych nowych wiadomości.</h3>
            @else
                <h3>Masz {{ $num_of_messages }} nieprzeczytanych wiadomości.</h3>
                <a role="button" href="{{ route('messages.index') }}">Przeczytaj wiadomości</a>
            @endif
        @endif
        <hr>
        <details>
            <summary class="contrast" role="button">Ustawienia konta</summary>
            <div class="grid">
                <p> Zmiana numeru telefonu, hasła, imienia i nazwiska. </p>
                <a role="button" href="{{ route('profile') }}">Edytuj dane konta</a>
            </div>
            @if (Auth::user()->role == 'dentist')
                <hr>
                <div class="grid">
                    <p> Edytuj profil lekarza. </p>
                    <a role="button" href="{{ route('offers.edit') }}">Edytuj profil lekarza</a>
                </div>
            @endif
            <hr>
        </details>
    </div>

@endsection
