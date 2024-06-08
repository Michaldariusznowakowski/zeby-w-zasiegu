@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Kalendarz')
@section('content')
    <div class="container">
        <div class="calendar">
            <div class="grid">
                <a role="button" href="{{ route('appointment.calendar.at', $previous_date) }}">Poprzedni</a>
                <h1 class="center">{{ $month }} {{ $year }}</h1>
                <a role="button" href="{{ route('appointment.calendar.at', $next_date) }}">Następny</a>
            </div>
            @if (str_contains($today, $year . '-' . $month) == false)
                <a role="button" href="{{ route('appointment.calendar') }}">Dzisiaj</a>
            @endif
            <table id="calendar">
                <thead>
                    <tr>
                        <th>Pn</th>
                        <th>Wt</th>
                        <th>Śr</th>
                        <th>Cz</th>
                        <th>Pt</th>
                        <th>So</th>
                        <th>Nd</th>
                    </tr>
                </thead>
                <tbody id="calendar-body">
                    @if ($number_of_days_to_skip != 0)
                        <tr>
                    @endif
                    @for ($i = 0; $i < $number_of_days_to_skip; $i++)
                        <td></td>
                    @endfor
                    @foreach ($calendar as $day)
                        @if ($day['day_of_week'] == 0 && $day['day'] != 1)
                            <tr>
                        @endif
                        <td>
                            <article @if ($today == $year . '-' . $month . '-' . $day['day']) class="pico-background-orange-100" @endif>
                                <header>
                                    {{ $day['day'] }}
                                </header>
                                @if ($day['appointments'] != null && count($day['appointments']) > 0)
                                    <p>Wizyty: {{ count($day['appointments']) }}</p>
                                    <a
                                        href="{{ route('appointment.calendar.day', $year . '-' . $month . '-' . $day['day']) }}">Zobacz</a>
                                @else
                                    <p>Brak wizyt</p>
                                @endif
                            </article>
                        </td>
                        @if ($day['day_of_week'] == 6)
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            <h1>Nadchodzące wizyty</h1>
            @if ($events != null && count($events) > 0)
                <div class="container">
                    @foreach ($events as $event)
                        <article>
                            <header>
                                <h2>Od {{ $event['start_date'] }} do {{ $event['end_date'] }}</h2>
                            </header>
                            <p>Opis: {{ $event['description'] }}</p>
                            <p>Status:
                                @if ($event['cancelled'] == 1)
                                    Odwołana
                                @else
                                    @if ($event['confirmed'] == 1)
                                        Potwierdzona
                                    @else
                                        Niepotwierdzona
                                    @endif
                                @endif
                            </p>
                            <a
                                href="{{ route('appointment.calendar.day', Date::parse($event['start_date'])->format('Y-m-d')) }}#event_{{ $event['id'] }}
                                ">Zobacz</a>
                        </article>
                    @endforeach
                </div>
            @else
                <h2>Brak zaplanowanych wizyt</h2>
            @endif
        </div>
    @endsection
