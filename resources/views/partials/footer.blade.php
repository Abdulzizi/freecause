@php
    $locale = app()->getLocale();
    $fallbackLocale = config('app.fallback_locale', 'en');

    $service = app(App\Services\PageContentService::class);

    $layoutContent = $service->getPageWithFallback('layout', $locale, $fallbackLocale);

    $about  = $layoutContent['footer_about'] ?? '';
    $links  = $layoutContent['footer_links'] ?? '';
    $bottom = $layoutContent['footer_bottom'] ?? '';
@endphp


<section class="bg-[#1f2024]! text-white! font-[Arial,sans-serif]!">
    <div class="container max-w-[1300px]!">
        <div class="row">
            <div class="col-12">

                <div class="p-10! max-md:text-center!">

                    {{-- About Section --}}
                    <div class="[&_img]:max-w-[306px]! [&_img]:mb-5! [&_p]:text-[15px]! [&_p]:mb-0! [&_p]:text-[#f2f2f2]! [&_a]:text-white! [&_a]:underline!">
                        {!! \App\Services\ContentRenderer::render($about) !!}
                    </div>

                    {{-- Links Section --}}
                    <div class="flex! flex-row! gap-x-5! pt-5! pb-[30px]! mt-[30px]! max-md:flex-col! max-md:items-center! [&_a]:text-white! [&_a]:no-underline! [&_a]:text-sm! [&_a]:hover:underline!">
                        {!! \App\Services\ContentRenderer::render($links) !!}
                    </div>

                </div>

                {{-- Bottom Bar --}}
                <div class="border-t! border-white/20! text-center! p-5! text-[15px]! text-[#f2f2f2]!">
                    {!! \App\Services\ContentRenderer::render($bottom) !!}
                </div>

            </div>
        </div>
    </div>
</section>
