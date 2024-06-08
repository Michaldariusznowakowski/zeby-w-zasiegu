@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Strona Powitalna')
@section('content')
    <div style="background-image: url('{{ asset('images/hero.webp') }}')" class="hero-container">
        <div class="hero-text">
            <h1>
                W naszej ofercie znajdziesz szeroki wybór usług stomatologicznych.<br />
                Wszystkie zabiegi wykonujemy z najwyższą starannością i dbałością o komfort pacjenta.<br />
                Załóż konto i umów się na wizytę już dziś!<br /> </h1>
            <a href="{{ route('login') }}
            " class="button">Zaloguj się</a>
        </div>
    </div>
    <div class="container">
        <h1>Czym są Zęby w Zasięgu?</h1>
        <p> Oferujemy Państwu szeroki zakres usług stomatologicznych, jesteśmy dostępni w całej Polsce. </p>
        <p> Współpracujemy z najlepszymi specjalistami w kraju. </p>
        <p> W naszej ofercie znajdą Państwo m.in. </p>
        <ul>
            <li>Implanty</li>
            <li>Wybielanie zębów</li>
            <li>Stomatologia estetyczna</li>
            <li>Ortodoncja</li>
            <li>Stomatologia zachowawcza</li>
            <li>Protetyka</li>
            <li>Chirurgia stomatologiczna</li>
            <li>Endodoncja</li>
            <li>Periodontologia</li>
        </ul>
        <p> Szczegółowy zakres usług znajdą Państwo w informacji o gabinecie. </p>
        <h2> Dlaczego warto skorzystać z naszych usług? </h2>
        <ul>
            <img src="{{ asset('images/why.webp') }}" alt="Dlaczego warto?" class="small" />
            <li>Jako jedyna platforma w Polsce gwarantujemy że wszystkie wiadomości i dane przesyłane przez naszych klientów
                są
                szyfrowane i przechowywane w bezpiecznym miejscu. </li>
            <li>Założenie konta jest darmowe i niezobowiązujące. </li>
            <li>Wszystkie zabiegi są wykonywane przez najlepszych specjalistów w kraju. </li>
            {{-- <li>Jawne ceny zabiegów. </li> --}}
            {{-- <li>Anonimowe oceny gabinetów, dzięki czemu możesz sprawdzić opinie innych pacjentów i wybrać najlepszy gabinet
                dla
                siebie. </li> --}}
        </ul>
        {{-- <h3> Jesteś dentystą? </h3>
        <img src="{{ asset('images/dentist.webp') }}" alt="Dentysta" />
        <p><b> Dołącz do nas i zwiększ swoją bazę klientów. </b></p>
        <ul>
            <li> Miesięczna opłata za korzystanie z naszej platformy to tylko 50 zł.
                Pobieramy opłatę tylko wtedy, gdy w miesiącu wykonasz zabieg. </p>
            <li> Bezpłatny okres próbny trwa 30 dni. </li>
            <li> Całodobowa bezpłatna pomoc techniczna. </li>
            <li> Intuicyjny panel zarządzania umówionymi wizytami. </li>
        </ul>
        <a href="#" class="button">Dołącz do nas</a> --}}
    </div>
@endsection
