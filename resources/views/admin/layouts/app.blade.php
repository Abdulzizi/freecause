<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('legacy/images-v2/freecause_logo_icon_clear.png') }}">
    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/admin.js'])
    @stack('head')
</head>

<body class="bg-[#f9fafb]!">
    <x-blatui.sidebar-provider class="bg-[#f9fafb]!">
        @include('admin.partials.sidebar')

        <x-blatui.sidebar-inset class="min-h-[calc(100vh-70px)]!">
            <header class="flex flex-wrap items-center justify-between gap-2 border-b! border-border! bg-surface! px-3.5! py-2.5! text-sm! box-border! max-md:px-2.5! max-md:py-1.5! max-md:text-[13px]!">
                <div class="flex items-center gap-2">
                    <x-blatui.sidebar-trigger />
                    <a class="text-brand! no-underline! whitespace-nowrap! hover:underline!" href="{{ route('admin.dashboard') }}">Dashboard</a>
                </div>
                <div class="flex items-center gap-2 text-muted! max-md:gap-1">
                    <span class="whitespace-nowrap!">v 1.2.1</span>
                    <span class="max-md:hidden!">|</span>
                    <span class="max-md:hidden!">support</span>
                    <x-blatui.dropdown-menu>
                        <x-blatui.dropdown-menu-trigger>
                            <button type="button" class="fc-abtn ms-1">
                                {{ admin_user()?->name ?? 'admin' }}
                                <x-lucide-chevron-down class="size-3.5" aria-hidden="true" />
                            </button>
                        </x-blatui.dropdown-menu-trigger>
                        <x-blatui.dropdown-menu-content align="end" class="w-44!">
                            <x-blatui.dropdown-menu-label class="text-muted!">{{ admin_user()?->name ?? 'admin' }}</x-blatui.dropdown-menu-label>
                            <x-blatui.dropdown-menu-separator />
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <x-blatui.dropdown-menu-item type="submit">exit</x-blatui.dropdown-menu-item>
                            </form>
                        </x-blatui.dropdown-menu-content>
                    </x-blatui.dropdown-menu>
                </div>
            </header>

            <div class="min-w-0! flex-1! box-border! p-2.5! px-3! max-md:p-3!">
                @yield('content')
            </div>
        </x-blatui.sidebar-inset>
    </x-blatui.sidebar-provider>

    <x-blatui.sonner />
    <x-blatui.sonner-flash />
    <x-admin.confirm-dialog />

    @foreach ($errors->all() as $err)
        <div hidden data-admin-flash="error" data-message="{{ $err }}"></div>
    @endforeach

    <x-blatui.back-to-top />
</body>

</html>
