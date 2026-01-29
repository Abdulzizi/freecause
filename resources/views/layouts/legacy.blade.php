<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Freecause')</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <link rel="stylesheet"
        href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.min.css" />

    <link rel="stylesheet" href="{{ asset('legacy/css-v2/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy/css-v2/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy/css-v2/slick-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy/css-v2/style.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    @stack('head')
</head>

<body class="@yield('body_class', '')">
    @include('partials.navbar')

    <main>
        @yield('content')
    </main>

    <div id="wp-remote-footer"></div>
    <script>
        (function () {
            fetch('https://www.freecause.com/magazine/wp-json/global/v2/footer')
                .then(res => res.json())
                .then(data => { if (data.html) document.getElementById('wp-remote-footer').innerHTML = data.html; })
                .catch(() => { });
        })();
    </script>

    <script src="{{ asset('legacy/js-v2/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('legacy/js-v2/slick.js') }}"></script>

    @stack('scripts')
</body>

</html>
