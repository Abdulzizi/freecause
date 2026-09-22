<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('legacy/images-v2/freecause_logo_icon_clear.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('head')
</head>

<body>
    <div class="fc-topbar">
        <div class="fc-topbar-right">
            <button class="fc-menu-toggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="fc-menu" onclick="toggleSidebar()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                Menu
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
                <form method="POST" action="{{ route('admin.logout') }}" style="display:inline" class="fc-logout-form">
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
            <button class="fc-toast-close" onclick="this.parentElement.remove()" aria-label="Dismiss" title="Dismiss">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            <span class="fc-toast-icon">
                @if(session('success'))
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                @elseif(session('warning'))
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                @else
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                @endif
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
            /* #5: lazy-load CKEditor only when needed */
            if (document.querySelector('textarea.fc-editor')) {
                var s = document.createElement('script');
                s.src = 'https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js';
                s.onload = function() {
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
                };
                document.head.appendChild(s);
            }

            /* #2: toast auto-dismiss (safe even with close button) */
            const toast = document.getElementById('fc-toast');
            if (toast) {
                setTimeout(() => toast.classList.add('show'), 50);

                const isError = toast.classList.contains('error');
                const timeout = isError ? 8000 : 3000;

                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.classList.remove('show');
                        setTimeout(() => toast.remove(), 300);
                    }
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

    <button class="fc-back-top" id="fc-back-top" aria-label="Back to top" onclick="window.scrollTo({top:0,behavior:'smooth'})">↑</button>
    <script>
        (function() {
            var btn = document.getElementById('fc-back-top');
            if (!btn) return;
            window.addEventListener('scroll', function() {
                btn.style.display = window.scrollY > 400 ? 'flex' : 'none';
            });
        })();
    </script>

</body>

</html>
