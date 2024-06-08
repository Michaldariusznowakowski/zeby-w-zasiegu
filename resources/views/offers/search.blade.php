@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Wyszukaj lekarza')
@section('api_token', true)
@section('content')
    <div class="container">
        <h1>Wyszukaj lekarza</h1>
        <article>
            <fieldset role="group">
                <input type="text" id="search" name="search" placeholder="Wpisz miejscowość/adres">
                <a id="findLocation" class="find-gps" onclick="findLocation()"><img src="/images/gps.svg" /></a>
            </fieldset>
            <label for="search">Wybierz odległość</label>
            <input type="range" id="range" min="1" max="100" value="10"
                oninput="updateRange(this.value)">
            <p id="rangeValue">10KM</p>
            <button id="searchButton" class="button-green" onclick="searchLocation()">Szukaj</button>
        </article>
        <div class="map" id="map" hidden="true"></div>
        {{-- <h2>Wyniki wyszukiwania:</h2> --}}
        <div id="results">
        </div>
        <hr>
    </div>

@endsection
@vite('resources/js/search.ts')
