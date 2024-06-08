@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Testowanie')
@section('content')
    <div class="container">
        {{-- wasmTest this file is used to check speed of the encryption decryption process --}}
        <div class="console">
        </div>
        <h1>Test szybkości szyfrowania i deszyfrowania plików</h1>
        <p>Wybierz pliki do przetestowania szybkości szyfrowania i deszyfrowania</p>
        <p>1MB</p>
        <input type="file" id="file1mb" name="file1mb" />
        <p>5MB</p>
        <input type="file" id="file5mb" name="file5mb" />
        <p>10MB</p>
        <input type="file" id="file10mb" name="file10mb" />
        <p>50MB</p>
        <input type="file" id="file50mb" name="file50mb" />
        <p>100MB</p>
        <input type="file" id="file100mb" name="file100mb" />
        <p>500MB</p>
        <input type="file" id="file500mb" name="file500mb" />
        <p>1GB</p>
        <input type="file" id="file1gb" name="file1gb" />
        <button id="start" onclick="start()">Rozpocznij test</button>
        <button id="dowloadEncrypted" onclick="downloadEncrypted()">Pobierz zaszyfrowane pliki</button>
        <button id="dowloadDecrypted" onclick="downloadDecrypted()">Pobierz odszyfrowane pliki</button>
    </div>
    @vite('resources/js/wasmTest.ts')

@endsection
