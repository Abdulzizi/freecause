<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $locale = app()->getLocale();

        $globalMeta = app(App\Services\PageContentService::class)->getPage('global', $locale);

        $metaSuffix = $globalMeta['meta_title_suffix'] ?? ' - FreeCause';
        $metaDescription =
            $globalMeta['meta_description'] ??
            'FreeCause - Online petition platform to launch and support causes worldwide.';
        $metaKeywords = $globalMeta['meta_keywords'] ?? 'petitions, activism, online petition, freecause';
        $headExtra = $globalMeta['head_additional_html'] ?? '';
        $footerExtra = $globalMeta['footer_additional_html'] ?? '';
    @endphp

    <title>
        @hasSection('title')
            @yield('title'){{ $metaSuffix }}
        @else
            FreeCause{{ $metaSuffix }}
        @endif
    </title>

    <link rel="icon" type="image/png" href="{{ asset('legacy/images-v2/freecause_logo_icon_clear.png') }}">

    <meta name="description" content="{{ strip_tags($metaDescription) }}">
    <meta name="keywords" content="{{ strip_tags($metaKeywords) }}">

    <meta property="og:site_name" content="FreeCause">
    <meta property="og:type" content="website">
    @hasSection('og_title')
        <meta property="og:title" content="@yield('og_title')">
        <meta property="og:description" content="@yield('og_description')">
        <meta property="og:image" content="@yield('og_image')">
        <meta property="og:url" content="@yield('og_url', request()->url())">
    @else
        <meta property="og:title" content="@yield('title', 'FreeCause')">
        <meta property="og:description" content="{{ strip_tags($metaDescription) }}">
        <meta property="og:url" content="{{ request()->url() }}">
    @endif

    {!! $headExtra !!}

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <link rel="stylesheet"
        href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.min.css" />

    <link rel="stylesheet" href="{{ asset('legacy/css-v2/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy/css-v2/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy/css-v2/slick-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy/css-v2/style.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">

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

    {!! $footerExtra !!}

    <script src="{{ asset('legacy/js-v2/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('legacy/js-v2/slick.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    @stack('scripts')

    {!! \App\Support\Settings::get('inject_body_html', '') !!}

    @include('partials.footer')
</body>

</html>