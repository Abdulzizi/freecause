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
        <div class="fc-topbar-right">
            <button class="fc-menu-toggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="fc-menu" onclick="toggleSidebar()">
                <i class="fa fa-bars"></i> Menu
            </button>
        </div>
        <div class="fc-topbar-left">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="sep">|</span> v 1.2.1
            <span class="sep">|</span> support
            <span class="sep">|</span>
            <div class="fc-user-info">
                <strong>{{ admin_user()?->name ?? 'admin' }}</strong>
                <span class="sep">|</span>
                <form method="POST" action="{{ route('admin.logout') }}" style="display:inline">
                    @csrf
                    <button type="submit" class="fc-btn">exit</button>
                </form>
            </div>
        </div>
    </div>

    <div class="fc-menu-backdrop" id="fc-menu-backdrop" onclick="closeSidebar()"></div>

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
        const sidebar = document.getElementById('fc-menu');
        const backdrop = document.getElementById('fc-menu-backdrop');
        const toggleBtn = document.querySelector('.fc-menu-toggle');
        let lastFocused = null;

        function openSidebar() {
            if (!sidebar || !backdrop) return;
            lastFocused = document.activeElement;
            sidebar.classList.add('show');
            backdrop.classList.add('show');
            if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            sidebar.focus();
        }

        function closeSidebar() {
            if (!sidebar || !backdrop) return;
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
            if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
            if (lastFocused) lastFocused.focus();
        }

        function toggleSidebar() {
            if (sidebar?.classList.contains('show')) closeSidebar();
            else openSidebar();
        }

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

            const toast = document.getElementById('fc-toast');
            if (toast) {
                setTimeout(() => toast.classList.add('show'), 50);

                const isError = toast.classList.contains('error');
                const timeout = isError ? 5000 : 2000;

                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, timeout);
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && sidebar?.classList.contains('show')) {
                    closeSidebar();
                }
            });

            if (sidebar) {
                sidebar.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function () {
                        if (window.innerWidth <= 768) closeSidebar();
                    });
                });
            }

            sidebar?.querySelectorAll('.fc-group').forEach((group, i) => {
                const title = group.querySelector('.group-title');
                if (!title) return;
                const key = 'fc-group-' + i;
                if (localStorage.getItem(key) === '1') group.classList.add('collapsed');
                title.addEventListener('click', () => {
                    group.classList.toggle('collapsed');
                    localStorage.setItem(key, group.classList.contains('collapsed') ? '1' : '0');
                });
            });
        });
    </script>

</body>

</html>
