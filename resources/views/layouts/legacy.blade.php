<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $locale = app()->getLocale();

        $globalMeta = app(App\Services\PageContentService::class)->getPage('global', $locale);

        $metaSuffix = $globalMeta['meta_title_suffix'] ?? ' - FreeCause';
        $metaDescription = $globalMeta['meta_description'] ?? 'FreeCause - Online petition platform to launch and support causes worldwide.';
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

    <meta name="description" content="{{ strip_tags($metaDescription) }}">
    <meta name="keywords" content="{{ strip_tags($metaKeywords) }}">

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

    @if(session('success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0 shadow" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif

    {!! \App\Support\Settings::get('inject_head_html', '') !!}
</head>

<body class="@yield('body_class', '')">
    @include('partials.navbar')

    <main>
        @yield('content')
    </main>

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toastEl = document.getElementById('successToast');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl, { delay: 5000 });
                toast.show();
            }
        });
    </script>
    @endif

    {!! $footerExtra !!}

    <script src="{{ asset('legacy/js-v2/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('legacy/js-v2/slick.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    @stack('scripts')

    {!! \App\Support\Settings::get('inject_body_html', '') !!}

    @include('partials.footer')
</body>

</html>
