<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $locale = app()->getLocale();
        $fallbackLocale = config('app.fallback_locale', 'en');

        $pageContentService = app(App\Services\PageContentService::class);
        $globalMeta = $pageContentService->getPage('global', $locale);
        $homeMeta = $pageContentService->getPage('home', $locale);
        $globalMetaDefault = $locale === $fallbackLocale ? $globalMeta : $pageContentService->getPage('global', $fallbackLocale);
        $homeMetaDefault = $locale === $fallbackLocale ? $homeMeta : $pageContentService->getPage('home', $fallbackLocale);

        $metaSuffix = $globalMeta['meta_title_suffix'] ?? ($globalMetaDefault['meta_title_suffix'] ?? ' - FreeCause');
        $metaDescription =
            $globalMeta['meta_description'] ??
            ($homeMeta['meta_description'] ??
            ($globalMetaDefault['meta_description'] ??
            ($homeMetaDefault['meta_description'] ??
            'FreeCause - Online petition platform to launch and support causes worldwide.')));
        $metaKeywords =
            $globalMeta['meta_keywords'] ??
            ($homeMeta['meta_keywords'] ??
            ($globalMetaDefault['meta_keywords'] ??
            ($homeMetaDefault['meta_keywords'] ??
            'petitions, activism, online petition, freecause')));
        $headExtra =
            $globalMeta['head_additional_html'] ??
            ($homeMeta['head_additional_html'] ??
            ($globalMetaDefault['head_additional_html'] ??
            ($homeMetaDefault['head_additional_html'] ?? '')));
        $footerExtra = $globalMeta['footer_additional_html'] ?? ($globalMetaDefault['footer_additional_html'] ?? '');
        $canonicalPageUrl = canonical_url().(request()->getQueryString() ? '?'.request()->getQueryString() : '');
    @endphp

    <title>
        @hasSection('title')
            @php
                $yieldedTitle = trim($__env->getSection('title'));
                $finalSuffix = str_ends_with($yieldedTitle, $metaSuffix) ? '' : $metaSuffix;
            @endphp
            {{ $yieldedTitle }}{{ $finalSuffix }}
        @else
            FreeCause{{ $metaSuffix }}
        @endif
    </title>

    <link rel="icon" type="image/png" href="{{ asset('legacy/images-v2/freecause_logo_icon_clear.png') }}">

    <meta name="description" content="{{ strip_tags($metaDescription) }}">
    <meta name="keywords" content="{{ strip_tags($metaKeywords) }}">

    <link rel="canonical" href="{{ $canonicalPageUrl }}">

    <meta property="og:site_name" content="FreeCause">
    <meta property="og:type" content="website">
    @hasSection('og_title')
        <meta property="og:title" content="@yield('og_title')">
        <meta property="og:description" content="@yield('og_description')">
        <meta property="og:image" content="@yield('og_image', asset('legacy/images-v2/freecause_logo_icon_clear.png'))">
        <meta property="og:url" content="@yield('og_url', $canonicalPageUrl)">
    @else
        <meta property="og:title" content="@yield('title', 'FreeCause')">
        <meta property="og:description" content="{{ strip_tags($metaDescription) }}">
        <meta property="og:image" content="{{ asset('legacy/images-v2/freecause_logo_icon_clear.png') }}">
        <meta property="og:url" content="{{ $canonicalPageUrl }}">
    @endif

    {!! \App\Services\ContentRenderer::render($headExtra) !!}

    @if (config('services.adsense.enabled') && config('services.adsense.client'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('services.adsense.client') }}" crossorigin="anonymous"></script>
    @endif

    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://ajax.googleapis.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://ajax.googleapis.com">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpQvzx0cegeju1MVsGrX5xXxAvs/HgeFs" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')

    {!! \App\Support\Settings::get('inject_head_html', '') !!}
</head>

<body class="@yield('body_class', '')">
    @include('partials.navbar')

    @php
        $announcementActive = \App\Support\Settings::get('announcement_active', false, 'global');
        $announcementText   = \App\Support\Settings::get('announcement_text', '', 'global');
    @endphp
    @if ($announcementActive && $announcementText)
        <div style="background:#c00;color:#fff;text-align:center;padding:8px 16px;font-size:0.95em;font-weight:600;">
            {!! e($announcementText) !!}
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    {!! \App\Services\ContentRenderer::render($footerExtra) !!}

    <script src="{{ asset('legacy/js-v2/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('legacy/js-v2/form-busy.js') }}" defer></script>

    @stack('scripts')

    @if (session('toast'))
        <div id="toast-flash" data-type="{{ session('toast')['type'] }}" data-message="{{ session('toast')['message'] }}" hidden></div>
    @endif

    @if (session('success'))
        <div id="flash-success" data-message="{{ session('success') }}" hidden></div>
    @endif

    @if (session('error'))
        <div id="flash-error" data-message="{{ session('error') }}" hidden></div>
    @endif

    {!! \App\Support\Settings::get('inject_body_html', '') !!}

    @include('partials.footer')
</body>

</html>
