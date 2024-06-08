@extends('partials.main.default')
@section('title', 'Zęby w Zasięgu Rejestracja')
@section('content')
    <div class="container">
        <h3>Zakładanie nowego konta</h3>
        <p> Tylko kilka prostych kroków dzieli Cię od umówienia się na wizytę. </p>
        <p> Wypełnij formularz rejestracyjny, a następnie potwierdź swój adres e-mail. </p>
        <p> Po potwierdzeniu adresu e-mail możesz zalogować się na swoje konto i umówić się na wizytę. </p>
        <span> Masz już konto? </span> <br />
        <a role="button" class="secondary" href="{{ route('login') }}">Zaloguj się</a> <br />
        <h4> Formularz rejestracyjny </h4>
        <form href="{{ route('register') }}" method="POST">
            @csrf
            <label for="name">Imię</label>
            <input type="text" name="name" id="name" placeholder="Imię" value="{{ old('name') }}" required />
            <x-errorinput name="name" />
            <label for="surname">Nazwisko</label>
            <input type="text" name="surname" id="surname" placeholder="Nazwisko" value="{{ old('surname') }}"
                required />
            <x-errorinput name="surname" />
            <label for="email">Adres e-mail</label>
            <input type="email" name="email" id="email" placeholder="Adres e-mail" value="{{ old('email') }}"
                required />
            <x-errorinput name="email" />
            <label for="password">Hasło</label>
            <input type="password" name="password" id="password" placeholder="Hasło" required />
            <x-errorinput name="password" />
            <label for="password_confirmation">Powtórz hasło</label>
            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Powtórz hasło"
                required />
            <x-errorinput name="password_confirmation" />
            <label for="telephone">Numer telefonu</label>
            <input type="telephone" name="telephone" id="telephone" placeholder="Numer telefonu"
                value="{{ old('telephone') }}" required />
            <x-errorinput name="telephone" />
            <fieldset>
                <label for="dentistCheckbox">Jestem dentystą</label>
                <input type="checkbox" name="dentistCheckbox" id="dentistCheckbox" onclick="toggleModal(event)"
                    data-target="dialogDentist" />
            </fieldset>
            <x-errorinput name="dentistCheckbox" />
            <input type="submit" value="Zarejestruj się" class="button" />
        </form>
        </details>


    </div>
    <dialog id="dialogDentist">
        <article>
            <h3> Czy na pewno jesteś lekarzem stomatologiem? </h3>
            <footer>
                <a href="#" role="button" data-target="dialogDentist dentistCheckbox" class="button secondary"
                    onclick="denySetCheckboxValue(event)">Jestem pacjentem</a>
                <a href="#"" role="button" data-target="dialogDentist dentistCheckbox" class="button"
                    onclick="acceptSetCheckboxValue(event)">Jestem dentystą</a>
            </footer>
        </article>
    </dialog>
@endsection
