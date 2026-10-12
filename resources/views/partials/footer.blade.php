@php
    $locale = app()->getLocale();
    $fallbackLocale = config('app.fallback_locale', 'en');

    $service = app(App\Services\PageContentService::class);

    $layoutContent = $service->getPageWithFallback('layout', $locale, $fallbackLocale);

    $about  = $layoutContent['footer_about'] ?? '';
    $links  = $layoutContent['footer_links'] ?? '';
    $bottom = $layoutContent['footer_bottom'] ?? '';
@endphp

<section class="site-footer bg-dark text-on-dark font-[Arial,sans-serif]">
    <div class="mx-auto w-full px-3 max-w-[1300px]">
        <div class="flex flex-wrap -mx-3">
            <div class="w-full shrink-0 grow-0 px-3">

                <div class="p-10 max-md:p-6 max-sm:p-4 max-md:text-center">

                    <div class="[&_img]:max-w-[306px] [&_img]:w-full [&_img]:h-auto [&_img]:mb-5 max-md:[&_img]:mx-auto max-md:[&_img]:max-w-[220px] [&_p]:text-sm [&_p]:mb-0 [&_p]:text-on-dark [&_a]:text-on-dark [&_a]:underline [&_a]:break-words">
                        {!! \App\Services\ContentRenderer::render($about) !!}
                    </div>

                    <div class="flex flex-row flex-wrap gap-x-5 gap-y-2 pt-5 pb-8 mt-8 max-md:flex-col max-md:items-center [&_a]:text-on-dark [&_a]:no-underline [&_a]:text-sm [&_a]:hover:underline">
                        {!! \App\Services\ContentRenderer::render($links) !!}
                    </div>

                </div>

                <div class="border-t border-on-dark/20 text-center p-5 max-sm:p-4 text-sm text-on-dark">
                    {!! \App\Services\ContentRenderer::render($bottom) !!}
                </div>

            </div>
        </div>
    </div>
</section>
