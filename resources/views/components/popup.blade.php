@if (session()->has('error') || session()->has('success'))
    <dialog open id="dialogMessage">
        <article>
            <header>
                <a href="#close" aria-label="Close" class="close" data-target="dialogMessage"
                    onClick="toggleModal(event)"></a>
                @if (session()->has('success'))
                    <h3>Akcja zakończona sukcesem</h3>
                @elseif(session()->has('error'))
                    <h3>Akcja zakończona niepowodzeniem</h3>
                @endif

            </header>
            <p>
                @if (session()->has('success'))
                    {{ session()->get('success') }}
                @elseif(session()->has('error'))
                    {{ session()->get('error') }}
                @endif
            <footer>
                <a href="#close" role="button" onClick="toggleModal(event) " data-target="dialogMessage">Zamknij</a>
        </article>
    </dialog>
@endif

@if (session()->has('errors'))
    <dialog open id="dialogErrors">
        <article>
            <header>
                <a href="#close" aria-label="Close" class="close" data-target="dialogErrors"
                    onClick="toggleModal(event)"></a>
                <h3>Wystąpiły błędy</h3>
            </header>
            <ul>
                @foreach (session()->get('errors')->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <footer>
                <a href="#close" role="button" onClick="toggleModal(event)" data-target="dialogErrors">Zamknij</a>
        </article>
    </dialog>
@endif
