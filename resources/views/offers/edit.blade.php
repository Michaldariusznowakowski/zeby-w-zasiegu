@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Edycja oferty')
@section('additional_meta')
    <meta name="meta-location" content="{{ $offer->latitude }}, {{ $offer->longitude }}">
    <meta name="meta-working-hours" content="{{ $offer->working_hours }}">
@endsection
@section('content')
    <div class="container">
        <div id="doctor-info">
            <div class="grid">
                <h1>Twoja oferta</h1>
                <a type="button" href="{{ route('offers.show', $offer->id) }}">Podgląd oferty</a>
            </div>
            <h1>{{ $user->name }} {{ $user->surname }}</h1>
            <form method="POST" action="{{ route('offers.update.photo') }}" enctype="multipart/form-data">
                @csrf
                <p>Aktualne zdjęcie:</p>
                <article>
                    <img class="profile-big" src="{{ asset('storage/' . $offer->image) }}" alt="Zdjęcie profilowe">
                    <div class="grid">
                        <input required type="hidden" name="id" value="{{ $offer->id }}">
                        <input required type="file" id="image" name="photo" accept="image/*" required>
                        <input type="submit" id="submit" value="Prześlij zdjęcie">
                    </div>
                </article>
            </form>
            <hr>
            <form method="POST" action="{{ route('offers.update.description') }}">
                @csrf
                <input required type="hidden" name="id" value="{{ $offer->id }}">
                <label for="description">Opis:</label>
                <fieldset role="group">
                    <textarea required id="description" name="description">{{ $offer->description }}</textarea>
                    <input type="submit" id="submit" value="Zmień opis">
                </fieldset>
            </form>
            <hr>
            <div class="grid">
                <div>
                    <form id="update-address" method="POST" action="{{ route('offers.update.address') }}">
                        @csrf
                        <label for="address">Adres:</label>
                        <fieldset role="group">
                            <input required type="hidden" name="id" value="{{ $offer->id }}">
                            <input required type="text" id="address" name="address" value="{{ $offer->address }}">
                            <input required hidden type="number" id="latitude" name="latitude"
                                value="{{ $offer->latitude }}">
                            <input required hidden type="number" id="longitude" name="longitude"
                                value="{{ $offer->longitude }}">
                        </fieldset>
                    </form>
                    <button onclick="getLocation()">Zmień adres</button>
                </div>
                <div id="map" class="map"></div>
            </div>
            <hr>
            <div class="grid">
                <p>Telefon kontaktowy: {{ $user->phone_number }}</p>
                <a href="{{ route('profile') }} ">Numer telefonu można zmienić w ustawieniach profilu</a>
            </div>
        </div>

        <hr>
        <h2>Oferowane usługi</h2>
        <p>Wprowadź nazwę usługi, opis, oraz przedział cenowy</p>
        <p>Jeżeli chcesz usunąć usługę, kliknij przycisk "Usuń usługę"</p>

        <hr>
        <form method="POST" action="{{ route('offers.add.service') }}">
            @csrf
            <label for="service">Usługa:</label>
            <fieldset role="group">
                <input required type="hidden" name="id" value="{{ $offer->id }}">
                <input required type="text" id="service" name="name" value="Usługa">
                <input type="submit" id="submit" value="Dodaj usługę">
            </fieldset>
        </form>
        @if ($services != null)
            <div class="grid-responsive">
                @foreach ($services as $service)
                    <article>
                        <form method="POST" action="{{ route('offers.update.service') }}">
                            @csrf
                            <input required type="hidden" name="id" value="{{ $offer->id }}">
                            <input required type="hidden" name="service_id" value="{{ $service->id }}">
                            <header>
                                <fieldset>
                                    <label for="service">Usługa:</label>
                                    <input required type="text" id="service" name="name"
                                        value="{{ $service->name }}">
                                </fieldset>
                            </header>
                            <fieldset <label for="description">Opis:</label>
                                <textarea id="description" name="description">{{ $service->description }}</textarea>
                            </fieldset>
                            <footer>
                                <fieldset>
                                    <p>Przedział cenowy:</p>
                                    <label for="minprice">Od:</label>
                                    <input required type="number" id="minprice" name="minprice"
                                        value="{{ $service->minprice }}">
                                    <label for="maxprice">Do:</label>
                                    <input required type="number" id="maxprice" name="maxprice"
                                        value="{{ $service->maxprice }}">
                                </fieldset>
                            </footer>
                            <input type="submit" id="submit" value="Edytuj">

                        </form>
                        <form method="POST" action="{{ route('offers.delete.service') }}">
                            <input required type="hidden" name="id" value="{{ $offer->id }}">
                            <input required type="hidden" name="service_id" value="{{ $service->id }}">
                            @csrf
                            <details>
                                <summary class="pico-color-red-550">Usuń usługę</summary>
                                <input type="submit" class="button-red" id="submit" value="Potwierdź usunięcie">
                            </details>
                        </form>
                    </article>
                @endforeach
            </div>
        @else
            <h4 class="pico-color-red-250">Nie znaleziono usług, użyj
                formularza powyżej, aby dodać nową usługę</h4>
        @endif
        <hr>
        <h2>Standardowa długość wizyty</h2>
        <details role="button" class="contrast">
            <summary>
                <p>Edytuj standardową długość wizyty</p>
            </summary>
            <p>Pacjent w trakcie wyzyty będzie mógł zarezerwować wizytę o wskazanej poprzez Ciebie długości</p>
            <p>Jeżeli długość wizyty nie wystarcza na wykonanie usługi, możesz skontaktować się z pacjentem
                w celu ustalenia szczegółów i przedłużenia wizyty</p>
            <form method="POST" action="{{ route('offers.update.duration') }}">
                <fieldset>
                    <span>Maksymalna długość wizyty to 120 minut, minimalna to 15 minut</span>
                    <label for="duration">Długość wizyty (w minutach):</label>
                    @csrf
                    <input required type="hidden" name="id" value="{{ $offer->id }}">
                    <input type="number" id="duration" name="duration"
                        value="{{ $offer->default_appointment_duration }}" min="15" max="120">
                </fieldset>
                <input type="submit" id="submit" value="Zapisz długość wizyty">
            </form>
        </details>
        <hr>
        <h2>Godziny pracy</h2>
        <details role="button" class="contrast">
            <summary>
                <p>Edytuj godziny pracy</p>
            </summary>
            <div>
                <p>Wprowadź godziny pracy w formacie HH:MM</p>
                <p>Jeśli dany dzień jest wolny od pracy, zaznacz pole "Zamknięte"</p>
            </div>
            <form autocomplete="off" method="POST" action="{{ route('offers.update.workinghours') }}">
                @csrf
                <input required type="hidden" name="id" value="{{ $offer->id }}">
                <div class="grid-responsive">
                    @php
                        $list = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                        $names = ['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela'];
                    @endphp
                    @foreach ($list as $key => $day)
                        <fieldset>
                            <p>{{ $names[$key] }}</p>
                            <label for="{{ $day }}Start">Od:</label>
                            <input type="time" id="{{ $day }}Start" name="{{ $day }}Start"
                                {{ $working_hours[$day]['ignore'] ? 'disabled' : '' }}
                                value="{{ $working_hours[$day]['start'] }}">
                            <label for="{{ $day }}End">Do:</label>
                            <input type="time" id="{{ $day }}End" name="{{ $day }}End"
                                {{ $working_hours[$day]['ignore'] ? 'disabled' : '' }}
                                value="{{ $working_hours[$day]['end'] }}">
                            <input type="checkbox" id="{{ $day }}" name="{{ $day }}Ignore"
                                oninput="toggleWorkingHours(event)" {{ $working_hours[$day]['ignore'] ? 'checked' : '' }}>
                            <label for="{{ $day }}Ignore">Zamknięte</label>
                        </fieldset>
                    @endforeach
                </div>
                <input type="submit" id="submit" value="Zapisz godziny pracy">
            </form>
        </details>
        <hr>
        <article>
            <header>
                <h2 class="pico-color-red-550"> Status
                    oferty: </h2>
            </header>
            <p>Oferta jest obecnie <strong>{{ $offer->active ? 'aktywna' : 'nieaktywna' }}</strong></p>
            <p>Jeśli chcesz zmienić status oferty, zaznacz odpowiednią opcję i kliknij przycisk "Zmień status"</p>
            <p>Zmiana statusu na "nieaktywna" spowoduje, że oferta nie będzie widoczna dla pacjentów,
                pacjenci nie będą mogli umawiać wizyt w ramach tej oferty. <br>
                Aktualnie umówione wizyty nie zostaną anulowane.<br>
                Wciąż będziesz mógł zarządzać umówionymi wizytami w ramach tej oferty, oraz
                komunikować się z pacjentami.</p>
            <p><strong>Uwaga!</strong><br> Aby oferta mogła być aktywna, musi posiadać przynajmniej jedną
                usługę.<br>
                Dodatkowo wszystkie dane oferty muszą być uzupełnione: zdjęcie, opis, adres, telefon.
            </p>


            <form method="POST" action="{{ route('offers.activate', $offer->id) }}">
                @csrf
                <input required type="hidden" name="id" value="{{ $offer->id }}">
                <fieldset>
                    {{-- radio active true false --}}
                    <input required type="radio" id="active" name="active" value="1"
                        {{ $offer->active ? 'checked' : '' }}>
                    <label for="active">Aktywna</label>
                    <input required type="radio" id="active" name="active" value="0"
                        {{ $offer->active ? '' : 'checked' }}>
                    <label for="active">Nieaktywna</label>

                </fieldset>
                <input class="pico-background-red-550" type="submit" id="submit" value="Zmień status">
                </fieldset>
            </form>
        </article>

        <hr>
    </div>

    @vite('resources/js/offers_edit.ts')
@endsection
