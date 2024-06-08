@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Modyfikacja wizyty')
@section('content')
    <a role="button" href="{{ url()->previous() }}">Powrót do poprzedniej strony</a>
    <div class="container">
        {{-- Powrót --}}
        <div>
            <form action="{{ route('appointment.move.search') }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{ $id }}">
                <h3>Wskaż termin wizyty</h3>
                <div class="grid">
                    <fieldset>
                        <label for="dateStart">Od:</label>
                        <input type="date" id="dateStart" name="dateStart">
                    </fieldset>
                    <fieldset>
                        <label for="dateEnd">Do:</label>
                        <input type="date" id="dateEnd" name="dateEnd">
                    </fieldset>
                    <fieldset>
                        <label for="duration">Czas trwania wizyty:</label>
                        <input type="number" id="duration" name="duration" value="30">
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
                        <form action="{{ route('appointment.move.save') }}" method="post">
                            @csrf
                            <input type="hidden" name="id" value="{{ $id }}">
                            <input type="hidden" name="date" value="{{ $appointment['date'] }}">
                            <input type="hidden" name="time" value="{{ $appointment['time'] }}">
                            <input type="hidden" name="duration" value="{{ $appointment['duration'] }}">
                            <fieldset role="group">
                                <h2>{{ $appointment['date'] }} {{ $appointment['time'] }}</h2>
                                <h3>Czas trwania: {{ $appointment['duration'] }} minut</h3>
                                <input type="submit" value="Przenieś wizytę"
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
