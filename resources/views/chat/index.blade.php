@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Rozmowy')
@section('content')
    <div class="container">
        <div style="background-image: url('{{ asset('images/hero-msg.webp') }}')" class="hero-container-small">
            <div class="hero-text">
                <h1>
                    Twoje rozmowy
                </h1>
            </div>
        </div>
        <hr>
        <a role="button" href="{{ url()->previous() }}">Powrót do poprzedniej strony</a>
        <hr>
        @if ($recipients == null)
            <h4>Przykro nam, ale nie masz żadnych rozmów, przejdź do ofert aby rozpocząć nową rozmowę </h4>
            <a role="button" href="{{ route('offers.search') }}">Oferty</a>
            <h4>Jeżeli potrzebujesz skontaktować się w związku z zaplanowaną wizytą, przejdź do kalendarza</h4>
            <a role="button" href="{{ route('appointment.calendar') }}">Kalendarz</a>
            <hr>
        @else
            <h1>Twoje rozmowy</h1>
            <div class="grid-responsive">
                @foreach ($recipients as $recipient)
                    <article @if ($recipient['has_unread_messages'] == true) class="pico-background-indigo-400" @endif>
                        <header>
                            @if ($recipient['offer_photo'] != null)
                                <img class="profile-big" src="{{ asset('storage/' . $recipient['offer_photo']) }}"
                                    alt="zdjęcie profilowe">
                            @endif
                            <h2>{{ $recipient['name'] }} {{ $recipient['surname'] }}</h2>
                            @if ($recipient['offer_link'] != null)
                                <a role="button" href="{{ $recipient['offer_link'] }}">Przejdź do oferty</a>
                            @endif
                        </header>
                        @if ($recipient['date_of_last_message'] != null)
                            <p>Ostatnia wiadomość: {{ $recipient['date_of_last_message'] }}</p>
                            @if ($recipient['has_unread_messages'] == true)
                                <p><strong>Masz nieprzeczytane wiadomości!</strong></p>
                            @endif
                        @else
                            <p>Brak wiadomości</p>
                        @endif
                        <a role="button" class="secondary"
                            href="{{ route('chat.with', $recipient['chatroom_id']) }}">Przejdź
                            do rozmowy</a>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
