<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Login')</title>
    <link rel="icon" type="image/png" href="{{ asset('legacy/images-v2/freecause_logo_icon_clear.png') }}">

    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/admin.js'])

    @stack('head')
</head>

<body>
    <div class="flex items-start justify-center min-h-screen bg-[#f9fafb] py-10 px-[15px]">
        <div class="w-full max-w-[1050px] px-5 pt-10 pb-[60px] max-md:px-3 max-md:py-6 max-md:pb-10">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>

</html>
