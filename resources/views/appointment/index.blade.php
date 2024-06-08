@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Umów wizytę')
@section('content')
    <a role="button" href="{{ route('offers.show', ['id' => $id]) }}">Powrót do oferty</a>
    <div class="container">
        {{-- Powrót --}}
        <div>
            <form action="{{ route('appointment.search', ['id' => $id]) }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{ $id }}">
                <h3>Wskaż preferowany termin wizyty</h3>
                <div class="grid">
                    <fieldset>
                        <label for="dateStart">Od:</label>
                        <input type="date" id="dateStart" name="dateStart">
                    </fieldset>
                    <fieldset>
                        <label for="dateEnd">Do:</label>
                        <input type="date" id="dateEnd" name="dateEnd">
                    </fieldset>
                </div>
                <input type="submit" value="Szukaj">
            </form>
        </div>
        <hr>
        @if ($appointments != null)
            <h2>W wybranym okresie dostępne są następujące terminy:</h2>
            <div id="appointments">
                @foreach ($appointments as $appointment)
                    <div>
                        <hr>
                        <form action="{{ route('appointment.saveAppointmentFromPatient') }}" method="post">
                            @csrf
                            <input type="hidden" name="doctor_id" value="{{ $id }}">
                            <input type="hidden" name="date" value="{{ $appointment['date'] }}">
                            <input type="hidden" name="time" value="{{ $appointment['time'] }}">
                            <input type="hidden" name="duration" value="{{ $appointment['duration'] }}">
                            <details id="moreInfo">
                                <summary>Dodatkowe informacje</summary>
                                <p> Wskaż powód wizyty:</p>
                                <input type="text" value="" required name="description">
                            </details>
                            <fieldset role="group">
                                <h2>{{ $appointment['date'] }} {{ $appointment['time'] }}</h2>
                                <h3>Czas trwania: {{ $appointment['duration'] }} minut</h3>
                                <input type="submit" value="Umów wizytę"
                                    onclick="
                                document.getElementById('moreInfo').open = true;
                                ">
                            </fieldset>
                        </form>
                        <hr>
                    </div>
                @endforeach
            </div>
        @else
            <h2>Nie wybrano terminu, lub nie ma dostępnych terminów w wybranym okresie</h2>
        @endif
    </div>
@endsection
