<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    @hasSection('additional_meta')
        @yield('additional_meta')
    @endif
    @hasSection('api_token')
        <meta name="api-token" content="{{ csrf_token() }}">
    @endif
    <title>@yield('title')</title>
    @vite('resources/css/style.css')
</head>

<body>
    @vite('resources/js/app.js')
    <div class="container-html">
        <div class="container-fluid">
            <nav>
                <ul>
                    <li>
                        @if (Auth::check() == false)
                            <a href="{{ route('welcome') }}">
                            @else
                                <a href="{{ route('home') }}">
                        @endif
                        <strong class="web-name">Zęby w Zasięgu</strong></a>
                    </li>
                </ul>
                <ul>
                    <li><a href="{{ route('welcome') }}" @if (Route::currentRouteName() == 'welcome') class="visited" @endif>Strona
                            główna </a>
                    <li>
                        <a href="{{ route('offers.search') }}"
                            @if (Route::currentRouteName() == 'offers.search') class="visited" @endif>Wyszukaj
                            lekarza</a>
                    </li>
                    @if (Auth::check() != true)
                        <li><a href="{{ route('login') }}"
                                @if (Route::currentRouteName() == 'login') class="visited" @endif>Zaloguj
                                się</a>
                        </li>

                        <li><a href="{{ route('register') }}"
                                @if (Route::currentRouteName() == 'register') class="visited" @endif>Zarejestruj
                                się</a>
                        </li>
                    @else
                        <li><a href="{{ route('appointment.calendar') }}"
                                @if (Route::currentRouteName() == 'appointment.calendar') class="visited" @endif>Wizyty</a>
                        <li><a href="{{ route('chat') }}"
                                @if (Route::currentRouteName() == 'chat') class="visited" @endif>Wiadomości</a>
                        </li>
                        <li><a href="{{ route('logout') }}"
                                @if (Route::currentRouteName() == 'logout') class="visited" @endif>Wyloguj
                                się</a>
                        </li>
                    @endif


                    </li>

                </ul>
            </nav>
        </div>
        <main>
            @yield('content')
        </main>
        <footer>
            <p>© 2024 Zęby w Zasięgu</p>
        </footer>
        @include('components.popup')
        @vite('resources/js/utils/modal.ts')
    </div>
</body>
