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
