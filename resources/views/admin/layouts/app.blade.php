<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('legacy/images-v2/freecause_logo_icon_clear.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body>
    <div class="flex! items-center! justify-between! flex-wrap! gap-2! px-3.5! py-2.5! text-sm! border-b! border-[#cfcfcf]! bg-white! min-h-[48px]! box-border! max-md:px-2.5! max-md:py-1.5! max-md:text-[13px]! max-md:gap-1!">
        <div class="flex! items-center! gap-2! flex-wrap! max-md:order-1!">
            <button class="fc-menu-toggle hidden! max-md:inline-flex! max-md:items-center! max-md:gap-1.5! max-md:bg-[#e6e6e6]! max-md:border! max-md:border-[#9e9e9e]! max-md:rounded! max-md:px-2.5! max-md:py-1.5! max-md:text-[13px]! max-md:font-bold! max-md:cursor-pointer!" aria-label="Toggle menu" aria-expanded="false" aria-controls="fc-menu" onclick="toggleSidebar()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                Menu
            </button>
        </div>
        <div class="flex! items-center! gap-2! flex-wrap! max-md:w-full! max-md:order-2! max-md:justify-start! max-md:pt-1! max-md:border-t! max-md:border-[#eee]! max-md:mt-1! max-md:gap-1! max-md:text-xs!">
            <a class="text-[#1a57c8]! no-underline! whitespace-nowrap! hover:underline!" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="text-[#777]! px-1.5! max-md:hidden!">|</span> v 1.2.1
            <span class="text-[#777]! px-1.5! max-md:hidden!">|</span> support
            <span class="text-[#777]! px-1.5! max-md:hidden!">|</span>
            <div class="flex! items-center! gap-1.5! max-md:hidden!">
                <strong>{{ admin_user()?->name ?? 'admin' }}</strong>
                <span class="text-[#777]! px-1.5!">|</span>
                <form method="POST" action="{{ route('admin.logout') }}" class="inline! max-md:hidden!">
                    @csrf
                    <button type="submit" class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-2.5! py-1! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! max-md:text-xs! max-md:px-3! max-md:py-1.5!">exit</button>
                </form>
            </div>
        </div>
    </div>

    <div class="fc-menu-backdrop max-md:fixed! max-md:inset-0! max-md:bg-black/40! max-md:z-[99]! max-md:transition-opacity! max-md:duration-200!" id="fc-menu-backdrop" onclick="closeSidebar()"></div>

    <div class="flex! gap-3! p-2.5! bg-white! max-md:flex-col! max-md:p-0! max-md:gap-0!">
        @include('admin.partials.sidebar')

        <div class="flex-1! bg-white! border! border-[#9e9e9e]! rounded! p-2.5! px-3! min-h-[calc(100vh-70px)]! box-border! min-w-0! max-md:w-full! max-md:border-0! max-md:rounded-none! max-md:p-3! max-md:min-h-[calc(100vh-48px)]!">
            @yield('content')
        </div>
    </div>

    @if(session('success') || session('warning') || $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if(session('success'))
                    toast.success({!! json_encode(session('success')) !!});
                @elseif(session('warning'))
                    toast.warning({!! json_encode(session('warning')) !!});
                @else
                    @foreach($errors->all() as $err)
                        toast.error({!! json_encode($err) !!});
                    @endforeach
                @endif
            });
        </script>
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
            if (sidebar) {
                sidebar.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function () {
                        if (window.innerWidth <= 768) closeSidebar();
                    });
                });

                sidebar.querySelectorAll('.fc-group').forEach((group, i) => {
                    const title = group.querySelector('.group-title');
                    if (!title) return;
                    const key = 'fc-group-' + i;
                    if (localStorage.getItem(key) === '1') group.classList.add('collapsed');
                    title.addEventListener('click', () => {
                        group.classList.toggle('collapsed');
                        localStorage.setItem(key, group.classList.contains('collapsed') ? '1' : '0');
                    });
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && sidebar?.classList.contains('show')) {
                    closeSidebar();
                }
            });

            /* lazy-load CKEditor only when an editor textarea is present */
            var editorAreas = document.querySelectorAll('textarea.fc-editor');
            if (editorAreas.length) {
                var s = document.createElement('script');
                s.src = 'https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js';
                s.onload = function () {
                    if (!window.CKEDITOR) return;
                    editorAreas.forEach(function (area, i) {
                        try {
                            if (!area.id) {
                                area.id = 'fc-editor-' + i;
                            }
                            CKEDITOR.replace(area.id, {
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
                        } catch (err) {
                            /* keep the plain textarea usable if the editor fails */
                        }
                    });
                };
                s.onerror = function () {};
                document.head.appendChild(s);
            }
        });
    </script>

    <button class="hidden! fixed! bottom-5! left-5! w-10! h-10! rounded-full! bg-[#333]! text-white! border-0! cursor-pointer! text-lg! items-center! justify-center! z-[90]! shadow-[0_2px_8px_rgba(0,0,0,0.2)]! transition-colors! hover:bg-[#555]! max-md:flex!" id="fc-back-top" aria-label="Back to top" onclick="window.scrollTo({top:0,behavior:'smooth'})">↑</button>
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
