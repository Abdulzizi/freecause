@php
    $locale = app()->getLocale();
    $fallbackLocale = config('app.fallback_locale', 'en');

    $service = app(App\Services\PageContentService::class);

    $layoutContent = $service->getPage('layout', $locale);

    if (empty($layoutContent)) {
        $layoutContent = $service->getPage('layout', $fallbackLocale);
    }

    $about  = $layoutContent['footer_about'] ?? '';
    $links  = $layoutContent['footer_links'] ?? '';
    $bottom = $layoutContent['footer_bottom'] ?? '';
@endphp

<style>
.global-footer {
    background: #1f2024;
    color: #fff;
    font-family: Arial, sans-serif;
}

.global-footer .container {
    max-width: 1300px;
    margin: 0 auto;
}

.footer-inner {
    padding: 40px;
}

.footer-logo {
    text-align: center;
}

.footer-about img {
    max-width: 306px;
    margin-bottom: 20px;
}

.footer-about p {
    font-size: 15px;
    margin-bottom: 0;
    color: #f2f2f2;
}

.footer-about a {
    color: #fff;
    text-decoration: underline;
}

.footer-links {
    display: flex;
    flex-direction: row;
    column-gap: 20px;
    padding-top: 20px;
    padding-bottom: 30px;
    margin-top: 30px;
}

.footer-links a {
    color: #fff;
    text-decoration: none;
    font-size: 14px;
}

.footer-links a:hover {
    text-decoration: underline;
}

.footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    padding: 20px;
    font-size: 15px;
    color: #f2f2f2;
}

@media (max-width: 768px) {
    .footer-inner {
        text-align: center;
    }

    .footer-links {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<section class="global-footer">
    <div class="container">
        <div class="row">
            <div class="col-12">

                <div class="footer-inner">

                    {{-- About Section --}}
                    <div class="footer-about">
                        {!! \App\Services\ContentRenderer::render($about) !!}
                    </div>

                    {{-- Links Section --}}
                    <div class="footer-links">
                        {!! \App\Services\ContentRenderer::render($links) !!}
                    </div>

                </div>

                {{-- Bottom Bar --}}
                <div class="footer-bottom">
                    {!! \App\Services\ContentRenderer::render($bottom) !!}
                </div>

            </div>
        </div>
    </div>
</section>
