@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Pobieranie pliku')
@section('api_token', true)
@section('additional_meta')
    <meta name="file_path" content="{{ $file_path }}">
    <meta name="file_name" content="{{ $file_name }}">
    <meta name="file_size" content="{{ $file_size }}">
    <meta name="message" content="{{ json_encode($message) }}">
    <meta name="file_content" content="{{ file_get_contents(storage_path('app/' . $file_path)) }}">
@endsection
@section('content')
    <div class="container">
        <div style="background-image: url('{{ asset('images/hero-filedownload.webp') }}" class="hero-container-small">
            <div class="hero-text">
                <h1>
                    Pobieranie pliku {{ $file_name }}
                </h1>
            </div>
        </div>
        <hr>
        <p>Proces odszyfrowania może potrwać kilka minut. Po zakończeniu procesu plik zostanie pobrany na Twój komputer.</p>
        <p>Czas oczekiwania zależy od wielkości pliku i mocy obliczeniowej Twojego komputera.</p>
        <hr>
        <h2>Plik: {{ $file_name }}</h2>
        <h3>Rozmiar: {{ $file_size }} MB</h3>
        <hr>
        <progress id="progress-bar" max="100" value="100"></progress>
        <button class="button" onclick="downloadFile()">Rozpocznij odszyfrowanie i pobieranie pliku</button>
    </div>
    @vite('resources/js/download.ts')
@endsection
