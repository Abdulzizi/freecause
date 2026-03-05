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

    @if (session('success'))

    @endif

    {!! \App\Support\Settings::get('inject_head_html', '') !!}
</head>

<body class="@yield('body_class', '')">
    @include('partials.navbar')

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

    @php
        $fcToasts = [];

        if (session('toast')) {
            $t = session('toast');
            $fcToasts[] = ['type' => $t['type'] ?? 'info', 'message' => $t['message']];
        }

        if (session('success')) {
            $fcToasts[] = ['type' => 'success', 'message' => session('success')];
        }

        if (isset($errors) && $errors->any() && !session('toast')) {
            $fcToasts[] = ['type' => 'error', 'message' => $errors->first()];
        }
    @endphp

    @if(!empty($fcToasts))
        <div id="fc-toast-container" style="position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;max-width:320px;">
            @foreach($fcToasts as $i => $t)
                @php
                    $bg = match($t['type']) {
                        'success' => '#28a745',
                        'error'   => '#dc3545',
                        'warning' => '#e0a800',
                        default   => '#343a40',
                    };
                @endphp
                <div class="fc-toast-item"
                    data-index="{{ $i }}"
                    style="background:{{ $bg }};
                        color:#fff;
                        padding:14px 18px;
                        border-radius:6px;
                        box-shadow:0 5px 15px rgba(0,0,0,0.2);
                        font-size:14px;
                        line-height:1.4;
                        opacity:0;
                        transform:translateY(-10px);
                        transition:all .3s ease;
                        cursor:pointer;"
                    onclick="this.style.opacity=0;this.style.transform='translateY(-10px)';setTimeout(()=>this.remove(),300);">
                    {{ $t['message'] }}
                </div>
            @endforeach
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.fc-toast-item').forEach(function (el, i) {
                    setTimeout(function () {
                        el.style.opacity = 1;
                        el.style.transform = 'translateY(0)';
                    }, 100 + i * 150);

                    setTimeout(function () {
                        el.style.opacity = 0;
                        el.style.transform = 'translateY(-10px)';
                        setTimeout(function () { el.remove(); }, 300);
                    }, 5000 + i * 150);
                });
            });
        </script>
    @endif
</body>

</html>