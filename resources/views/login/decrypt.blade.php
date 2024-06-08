@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Odszyfrowanie klucza')
@section('api_token', true)
@section('content')
    <div class="container">
        <h1> Wczytaj klucz szyfrujący </h1>
        <p> Klucz szyfrujący został przez ciebie wygenerowany podczas pierwszego logowania. </p>
        <p> Jest to plik w formie qrcode, który został zapisany na twoim dysku. </p>
        <div class="container-fluid">
            <img class="small" id="qrcodeImage1" hidden src="" alt="Klucz szyfrujący, część 1" />
            <label for="qrcodeFile1" class="button">Wczytaj z pliku, część 1</label>
            <input type="file" id="qrcodeFile1" accept="image/*" oninput="onLoadShowQR1(event)" />
            <img class="small" id="qrcodeImage2" hidden src="" alt="Klucz szyfrujący, część 2" />
            <label for="qrcodeFile2" class="button">Wczytaj z pliku, część 2</label>
            <input type="file" id="qrcodeFile2" accept="image/*" oninput="onLoadShowQR2(event)" />
            <p>Alternatywnie możesz wczytać klucz z pliku tekstowego.</p>
            <label for="txtFile3" class="button">Wczytaj z pliku, klucz tekstowy</label>
            <input type="file" id="txtFile3" accept=".txt" />
            <progress id="progressBar" max="100" hidden></progress>
            <input type="button" value="Wczytaj klucz" class="button loading" id="loadFiles" onclick="readFile(event)"
                disabled />
        </div>
        <p> Jeżeli utraciłeś klucz, możesz go zresetować. </p>
        <p> Uwaga! Spowoduje to utratę wszystkich wiadomości. </p>
        <a href="#" role="button" data-target="dialogWarning" class="button button-red"
            onclick="toggleModal(event)">Zresetuj
            klucz</a>
    </div>
    <dialog id="dialogInfo">
        <article>
            <h3> Informacja </h3>
            <p id="dialogText"> </p>
            <footer>
                <a href="#" role="button" data-target="dialogInfo" class="button-green"
                    onclick="toggleModal(event)">Zamknij</a>
            </footer>
        </article>
    </dialog>
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
    <dialog id="dialogWarning">
        <article>
            <h3> Uwaga! </h3>
            <p> Czy na pewno chcesz zresetować klucz? </p>
            <p> Spowoduje to utratę wszystkich wiadomości. </p>
            <footer>

                <form action="{{ route('login.purgeKeys') }}" method="post">
                    @csrf
                    <input type="submit" value="Usuń klucz" class="button-red" />
                    <a href="#" role="button" data-target="dialogWarning" class="button-green button-full-width"
                        onclick="toggleModal(event)">Anuluj</a>
                </form>
            </footer>
        </article>
    </dialog>
    @vite('resources/js/decrypt.ts')
@endsection
