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
        {{-- @if(auth()->check() && !auth()->user()->verified)
            <div class="position-fixed top-0 end-0 p-4" style="z-index: 1080;">
                <div id="verifyToast"
                    class="toast align-items-center text-dark bg-warning border-0 shadow-lg"
                    role="alert"
                    data-bs-delay="8000">

                    <div class="d-flex align-items-center">

                        <div class="toast-body d-flex align-items-center gap-2">

                            <i class="fa fa-exclamation-triangle text-dark"></i>

                            <div>
                                <div class="fw-semibold">
                                    Email not verified
                                </div>
                                <div style="font-size:13px;">
                                    Please verify your email to secure your account.
                                    <a href="{{ lroute('verification.resend') }}"
                                    class="fw-bold text-dark ms-1 text-decoration-underline">
                                        Resend
                                    </a>
                                </div>
                            </div>

                        </div>

                        <button type="button"
                                class="btn-close me-3"
                                data-bs-dismiss="toast">
                        </button>

                    </div>

                </div>
            </div>
        @endif --}}

        @yield('content')
    </main>

    <div id="wp-remote-footer"></div>

    <script>
        (function () {
            var SUPPORTED = ['en', 'fr', 'it'];

            function getLocaleFromPath() {
                var parts = (window.location.pathname || '/').split('/').filter(Boolean);
                var first = parts[0];
                return SUPPORTED.indexOf(first) !== -1 ? first : null;
            }

            function localizeWpFooterLinks(rootEl, locale) {
                if (!rootEl || !locale) return;

                var anchors = rootEl.querySelectorAll('a[href]');
                anchors.forEach(function (a) {
                    var href = a.getAttribute('href');
                    if (!href) return;

                    if (href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
                        return;
                    }

                    if (href.startsWith('http://') || href.startsWith('https://')) {
                        try {
                            var u = new URL(href);
                            if (u.hostname.endsWith('freecause.com')) {
                                href = u.pathname + (u.search || '') + (u.hash || '');
                            } else {
                                return;
                            }
                        } catch (e) {
                            return;
                        }
                    }

                    if (!href.startsWith('/')) return;

                    var parts = href.split('/').filter(Boolean);
                    if (parts.length && SUPPORTED.indexOf(parts[0]) !== -1) return;

                    a.setAttribute('href', '/' + locale + href);
                });
            }

            fetch('https://www.freecause.com/magazine/wp-json/global/v2/footer')
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data.html) return;

                    var el = document.getElementById('wp-remote-footer');
                    el.innerHTML = data.html;

                    // patch links AFTER injection
                    var locale = getLocaleFromPath();
                    localizeWpFooterLinks(el, locale);
                })
                .catch(function () { });
        })();

        // document.addEventListener('DOMContentLoaded', function () {
        //     var toastEl = document.getElementById('verifyToast');
        //     if (toastEl) {
        //         var toast = new bootstrap.Toast(toastEl);
        //         toast.show();
        //     }
        // });
    </script>

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
</body>

</html>
