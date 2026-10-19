<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

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
        $_canonicalParams = array_diff_key(request()->query(), array_flip(['status', 'view', 'page']));
        $canonicalPageUrl = canonical_url().($_canonicalParams !== [] ? '?'.http_build_query($_canonicalParams) : '');

        $ogTitle = trim((string) $__env->getSection('og_title', $__env->getSection('title', 'FreeCause')));
        $ogDescription = trim((string) $__env->getSection('og_description', strip_tags($metaDescription)));
        $ogUrl = trim((string) $__env->getSection('og_url', $canonicalPageUrl));
        $ogType = trim((string) $__env->getSection('og_type', 'website'));
        $ogImage = trim((string) $__env->getSection('og_image', asset('legacy/images-v2/freecause_logo_icon_clear.png')));
        if ($ogImage !== '' && ! str_starts_with($ogImage, 'http://') && ! str_starts_with($ogImage, 'https://')) {
            $ogImage = url($ogImage);
        }
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

    @if (request()->is('*/widgets/*', '*/widget-render/*', '*/oauth/*', '*/user/*') || request()->has('status') || request()->query('view') === 'pages')
        <meta name="robots" content="noindex, follow">
    @endif

    <meta property="og:site_name" content="FreeCause">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:url" content="{{ $ogUrl }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {!! \App\Services\ContentRenderer::render($headExtra) !!}

    @if (config('services.adsense.enabled') && config('services.adsense.client'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('services.adsense.client') }}" crossorigin="anonymous"></script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')

    @include('partials.analytics')
</head>

<body class="fc-public @yield('body_class', '')">
    <a href="#fc-main" class="fc-skip-link">{{ __('Skip to content') }}</a>
    @include('partials.navbar')

    @include('partials.ads-anchor')
    @include('partials.ads-floating')
    @unless (isset($exception))
        {{-- Rendered before the page body on purpose: the sticky anchor (mobile) and the
             side rail (desktop) emit the "mobile"/"skyscraper" zones, and Ads::renderOnce
             gives the zone to the first slot rendered in the request. Claiming them here
             keeps a page's own inline copy from winning and leaving the anchor empty.
              Both are position:fixed, so DOM order does not affect where they appear. --}}
        @include('partials.ads-rail')
        @include('partials.ads-rail-left')
    @endunless

    @php
        $announcementActive = \App\Support\Settings::get('announcement_active', false, 'global');
        $announcementText   = \App\Support\Settings::get('announcement_text', '', 'global');
    @endphp
    @if ($announcementActive && $announcementText)
        <div class="site-notice bg-brand text-white text-center px-4 py-2 text-sm font-semibold">
            {!! e($announcementText) !!}
        </div>
    @endif

    <main id="fc-main">
    @include('partials.ads-anchor')
    @include('partials.ads-floating')
    @unless (isset($exception))
            {{-- Top banner: desktop only. The 320x50 "mobile" zone is consumed by the
                 sticky anchor below and a zone must never appear twice on one page. --}}
            <div class="hidden lg:block text-center my-4"><x-ads.slot name="leaderboard" /></div>
        @endunless

        @if (session('profile_completion'))
        <div class="bg-brand-soft border border-brand text-ink px-4 py-2 text-sm text-center" role="status">
            {{ session('profile_completion') }} <a href="{{ lroute('profile') }}" class="underline font-semibold">{{ __('profile.finish_profile') }}</a>
        </div>
        @endif
        @yield('content')

        @include('partials.ads-mini', ['slot' => 'mobile_mini'])

    </main>

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
    @unless (isset($exception))
        @if (\App\Support\Ads::enabled() && \App\Support\Ads::slot('mobile') && ! empty(\App\Support\Ads::slot('mobile')['key']))
            <div class="fc-ad-anchor-spacer" aria-hidden="true"></div>
        @endif
    @endunless
    @include('partials.mobile-nav')
</body>

</html>
