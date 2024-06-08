@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Szyfrowanie klucza')
@section('api_token', true)
@section('content')
    <div class="container">
        <h1> Tworzenie klucza szyfrującego </h1>
        <h2> Wszystkie wiadomości i pliki przesyłane przez naszych klientów są szyfrowane i przechowywane w bezpiecznym
            miejscu. </h2>
        <p> Klucz szyfrujący jest niezbędny do odczytania wiadomości. </p>
        <p> Wciśnij w przycisk poniżej aby wygenerować klucz szyfrujący. </p>
        <p> Zapisz wygenerowane qr kody, oraz klucz w formie pliku tekstowego. </p>
        <p> Klucz w formie pliku tekstowego jest opcjonalny, ale zalecamy jego zapisanie. </p>
        <p> Przy każdym logowaniu poprosimy ciebie o podanie hasła i klucza szyfrującego. </p>
        <p> Po stronie serwera przechowujemy tylko klucz publiczny i twój adres email podpisany kluczem prywatnym, pozwala
            nam to na weryfikacje poprawności twojego klucza prywatnego. </p>
        <div class="container">
            <progress id="progressBar" max="100" hidden></progress>
            <div>
                <canvas hidden id="qrcodeCanvas1" width="200" height="200"></canvas>
            </div>

            <a role="button" hidden id="qrcodeDownload1" href="" download="qrcode.png">Pobierz 1
                część klucza szyfrujacego</a>
            <div>
                <canvas hidden id="qrcodeCanvas2" width="200" height="200"></canvas>
            </div>
            <a role="button" hidden id="qrcodeDownload2" href="" download="qrcode2.png">Pobierz 2
                część klucza szyfrujacego</a>

            <div class="container">
                <a hidden id="qrcodeDownload3" href="" download="qrcode.txt"><i>Opcjonalnie</i> Pobierz klucz
                    szyfrujacy w formie pliku tekstowego</a>
                <input hidden id="nextStep" class="button-green" type="button" value="Zapisałem klucz, chcę przejść dalej"
                    class="button" onclick="NextStep(event)" />
                <input type="button" id="generateNewKey" value="Generuj nowy klucz" class="button loading"
                    onclick="GenerateKey(event)" />
            </div>
        </div>
    </div>
    <dialog id="dialogError">
        <article>
            <h3> Uwaga! </h3>
            <p id="dialogText"> </p>
            <footer>
                <a href="#" role="button" data-target="dialogError" class="button"
                    onclick="toggleModal(event)">Zamknij</a>
            </footer>
        </article>
    </dialog>
    @vite('resources/js/encrypt.ts')
@endsection
