<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Login')</title>
    <link rel="icon" type="image/png" href="{{ asset('legacy/images-v2/freecause_logo_icon_clear.png') }}">

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>

    @stack('head')
</head>

<body>
    <div class="admin-auth-wrap">
        <div class="admin-auth-stage">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>

</html>
