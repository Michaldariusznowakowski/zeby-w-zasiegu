@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Oferta')
@section('additional_meta')
    <meta name="meta-location" content="{{ $offer->latitude }}, {{ $offer->longitude }}">
    <meta name="meta-photo" content="{{ asset('storage/' . $offer->image) }}">
@endsection
@section('content')
    <div class="container">
        @if ($offer->doctor_id == Auth::id())
            <div class="grid">
                <h1>Twoja oferta jest
                    @if ($offer->active == false)
                        <strong class="pico-color-red-550"> nieaktywna </strong>
                    @else
                        <strong class="pico-color-green-550"> aktywna </strong>
                    @endif

                </h1>
                <a type="button" href="{{ route('offers.edit', $offer->id) }}">Edytuj ofertę</a>
            </div>
        @endif
        <div id="doctor-info">
            <h1>{{ $user->name }} {{ $user->surname }}</h1>
            <img class="profile-big" src="{{ asset('storage/' . $offer->image) }}" alt="Zdjęcie profilowe">
            <h4>Opis:</h4>
            <p>{{ $offer->description }}</p>

            <h4>Adres:</h4>
            <a href="https://www.google.com/maps/search/?api=1&query={{ $offer->latitude }},{{ $offer->longitude }}"
                target="_blank">{{ $offer->address }}</a>
            <h4>Telefon kontaktowy:</h4>
            <p>{{ $user->phone_number }}</p>
            <h4>Rozpocznij rozmowę poprzez chat online:</h4>
            <a href="{{ route('chat.start', $offer->doctor_id) }}">Rozpocznij rozmowę</a>
            <h4>Godziny pracy:</h4>
            @php
                $list = ['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela'];
                $list2 = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            @endphp
            <div class="grid-responsive">
                @foreach ($list2 as $key => $day)
                    <article>
                        <header>
                            <h4>{{ $list[$key] }}</h4>
                        </header>
                        <p>
                            @if ($working_hours[$day]['ignore'] == true)
                                Nieczynne
                        </p>
                    @else
                        {{ $working_hours[$day]['start'] }} - {{ $working_hours[$day]['end'] }}</p>
                @endif
                </article>
                @endforeach
            </div>
            <hr>
            @if (Auth::user() && Auth::user()->role == 'patient')
                <fieldset role="group">
                    <h2>Sprawdź dostępne terminy i umów wizytę</h2>
                    <a role="button"
                        href="{{ route('appointment.searchAppointmentForm', ['id' => $offer->doctor_id]) }}">Umów
                        wizytę</a>
                </fieldset>
                <hr>
            @endif
            <div class="map" id="map"></div>
            <hr>
            <h2>Oferowane usługi</h2>
            @if ($services != null)
                <div class="grid-3">
                    @foreach ($services as $service)
                        <article>
                            <header>
                                <h3>{{ $service->name }}</h3>
                            </header>
                            <p>{{ $service->description }}</p>
                            <footer>
                                @if ($service->minprice == $service->maxprice)
                                    <p>Cena: {{ $service->minprice }}zł</p>
                                @else
                                    <p>Przedział cenowy: {{ $service->minprice }}-{{ $service->maxprice }}zł</p>
                                @endif
                            </footer>
                        </article>
                    @endforeach
                </div>
            @else
                <p>Brak oferowanych usług</p>
            @endif
            <hr>
        </div>
        @vite('resources/js/offers.ts')
    @endsection
