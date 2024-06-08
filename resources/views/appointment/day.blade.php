@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Kalendarz Podgląd dnia')
@section('content')
    <div class="container">
        <a role="button" href="{{ url()->previous() }}">Wróć</a>
        <h1>{{ $day }}.{{ $month }}.{{ $year }}</h1>
        <h1>Wizyty na ten dzień</h1>
        @if ($events != null && count($events) > 0)
            <div class="container">
                @foreach ($events as $event)
                    <article id="event_{{ $event['id'] }}">
                        <header>
                            <h2>Od {{ $event['start_date'] }} do {{ $event['end_date'] }}</h2>
                        </header>
                        <hr>
                        <p>
                            @if (Auth::user()->role == 'dentist')
                                Pacjent:
                            @else
                                Lekarz:
                            @endif
                            {{ $event['person']['name'] }} {{ $event['person']['surname'] }}
                        </p>
                        <hr>
                        <p>Numer telefonu:<b> {{ $event['person']['phone_number'] }}</b></p>
                        <hr>
                        <p>Opis: <b>{{ $event['description'] }}</b></p>
                        <hr>
                        <p>Status:
                            @if ($event['cancelled'] == 1)
                                <b class="pico-color-red-500">Odwołana</b>
                            @else
                                @if ($event['confirmed'] == 1)
                                    <b class="pico-color-green-500">Potwierdzona</b>
                                @else
                                    <b class="pico-color-yellow-500">Niepotwierdzona</b>
                                @endif
                            @endif
                            @if ($event['cancelled'] == 0)
                                <form action="{{ route('appointment.cancel') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $event['id'] }}">
                                    <button type="submit" class="button-red">Odwołaj wizytę</button>
                                </form>
                                <hr>
                            @endif
                            @if (Auth::user()->role == 'dentist' && $event['confirmed'] == 0 && $event['cancelled'] == 0)
                                <form action="{{ route('appointment.confirm') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $event['id'] }}">
                                    <button type="submit" class="button-green">Potwierdź wizytę</button>
                                </form>
                                <hr>
                                <p> Ustal z pacjentem nowy termin wizyty poprzez czat lub telefon i przenieś wizytę.</p>
                                <a href="{{ route('appointment.move', $event['id']) }}">Przenieś wizytę</a>
                                <hr>
                                <a href="{{ route('chat.start', $event['person']['id']) }}">Rozpocznij
                                    rozmowe</a>
                            @endif
                        </p>
                        <hr>
                        @if (Auth::user()->role == 'patient')
                            <a href="{{ route('offers.show', $event['offer_id']) }}">Zobacz
                                profil</a>
                            <hr>
                        @endif
                        <a href="{{ route('chat.start', $event['person']['id']) }}">Rozpocznij
                            rozmowe</a>
                    </article>
                @endforeach
            </div>
        @else
            <h2>Brak zaplanowanych wizyt</h2>
        @endif
    </div>
@endsection
