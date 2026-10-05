<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('legacy/images-v2/freecause_logo_icon_clear.png') }}">
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
    @stack('head')
</head>

<body>
    <div class="flex! items-center! justify-between! flex-wrap! gap-2! px-3.5! py-2.5! text-sm! border-b! border-[#cfcfcf]! bg-white! min-h-[48px]! box-border! max-md:px-2.5! max-md:py-1.5! max-md:text-[13px]! max-md:gap-1!">
        <div class="flex! items-center! gap-2! flex-wrap! max-md:order-1!">
            <x-ui.button type="button" variant="secondary" size="sm" class="fc-menu-toggle hidden! max-md:inline-flex! max-md:items-center! max-md:gap-1.5! max-md:bg-[#e6e6e6]! max-md:border! max-md:border-[#9e9e9e]! max-md:rounded! max-md:px-2.5! max-md:py-1.5! max-md:text-[13px]! max-md:font-bold! max-md:cursor-pointer!" aria-label="Toggle menu" aria-expanded="false" aria-controls="fc-menu" data-admin-sidebar-toggle>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                Menu
            </x-ui.button>
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
                    <x-ui.button type="submit" variant="secondary" size="sm" class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-2.5! py-1! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! max-md:text-xs! max-md:px-3! max-md:py-1.5!">exit</x-ui.button>
                </form>
            </div>
        </div>
    </div>

    <div class="fc-menu-backdrop max-md:fixed! max-md:inset-0! max-md:bg-black/40! max-md:z-[99]! max-md:transition-opacity! max-md:duration-200!" id="fc-menu-backdrop" data-admin-sidebar-close></div>

    <div class="flex! gap-3! p-2.5! bg-white! max-md:flex-col! max-md:p-0! max-md:gap-0!">
        @include('admin.partials.sidebar')

        <div class="flex-1! bg-white! border! border-[#9e9e9e]! rounded! p-2.5! px-3! min-h-[calc(100vh-70px)]! box-border! min-w-0! max-md:w-full! max-md:border-0! max-md:rounded-none! max-md:p-3! max-md:min-h-[calc(100vh-48px)]!">
            @yield('content')
        </div>
    </div>

    @if (session('success'))
        <div hidden data-admin-flash="success" data-message="{{ session('success') }}"></div>
    @elseif (session('warning'))
        <div hidden data-admin-flash="warning" data-message="{{ session('warning') }}"></div>
    @else
        @foreach ($errors->all() as $err)
            <div hidden data-admin-flash="error" data-message="{{ $err }}"></div>
        @endforeach
    @endif

    <x-ui.button type="button" variant="ghost" size="sm" class="hidden! fixed! bottom-5! left-5! w-10! h-10! rounded-full! bg-[#333]! text-white! border-0! cursor-pointer! text-lg! items-center! justify-center! z-[90]! shadow-[0_2px_8px_rgba(0,0,0,0.2)]! transition-colors! hover:bg-[#555]! max-md:flex!" id="fc-back-top" aria-label="Back to top">↑</x-ui.button>

</body>

</html>
