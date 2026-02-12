<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    @stack('head')
</head>

<body>
    <div class="fc-topbar">
        <a href="https://www.freecause.com" target="_blank">https://www.freecause.com</a>
        <span class="sep">|</span> v 1.2.1
        <span class="sep">|</span> support
        <span class="sep">|</span> <strong>{{ session('admin_username', 'admin') }}</strong>
        <span class="sep">|</span>

        <form method="POST" action="{{ route('admin.logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="fc-btn">exit</button>
        </form>
    </div>

    <div class="fc-frame">
        @include('admin.partials.sidebar')

        <div class="fc-panel">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>

</html>
