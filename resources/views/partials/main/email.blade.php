<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Zęby w Zasięgu</title>
</head>
<style>
    main {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    main>* {
        margin: 0 1rem;
    }
</style>

<body>
    <main>
        @yield('content')
    </main>

</body>

</html>
