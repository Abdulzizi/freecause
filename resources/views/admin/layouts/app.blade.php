<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('legacy/images-v2/freecause_logo_icon_clear.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    @stack('head')
</head>

<body>
    <div class="fc-topbar">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span class="sep">|</span> v 1.2.1
        <span class="sep">|</span> support
        <span class="sep">|</span> <strong>{{ admin_user()?->name ?? 'admin' }}</strong>
        <span class="sep">|</span>

        <form method="POST" action="{{ route('admin.logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="fc-btn">exit</button>
        </form>
    </div>

    <button class="fc-menu-toggle" onclick="document.querySelector('.fc-menu').classList.toggle('show')">
        <i class="fa fa-bars"></i> Menu
    </button>

    <div class="fc-frame">
        @include('admin.partials.sidebar')

        <div class="fc-panel">
            @yield('content')
        </div>
    </div>

    @if(session('success') || session('warning') || $errors->any())
        <div id="fc-toast" class="fc-toast {{ session('success') ? 'success' : 'error' }}">
            <span class="fc-toast-icon">
                {{ session('success') ? '✓' : '⚠' }}
            </span>
            <span class="fc-toast-text">
                @if(session('success'))
                    {{ session('success') }}
                @elseif(session('warning'))
                    {{ session('warning') }}
                @else
                    @foreach($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                @endif
            </span>
        </div>
    @endif

    @stack('scripts')

    <script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (document.querySelector('textarea.fc-editor')) {
                CKEDITOR.replace('content', {
                    toolbar: [
                        { name: 'clipboard', items: ['Undo', 'Redo'] },
                        { name: 'styles', items: ['Format'] },
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline'] },
                        { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight'] },
                        { name: 'links', items: ['Link', 'Unlink'] },
                        { name: 'insert', items: ['Image', 'Table'] },
                        { name: 'tools', items: ['Source'] }
                    ],
                    height: 350
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const toast = document.getElementById('fc-toast');
            if (!toast) return;

            setTimeout(() => toast.classList.add('show'), 50);

            const isError = toast.classList.contains('error');
            const timeout = isError ? 5000 : 2000;

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, timeout);
        });
    </script>

</body>

</html>
