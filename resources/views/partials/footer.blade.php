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
    <div class="mx-auto! w-full! px-3! max-w-[1300px]!">
        <div class="flex! flex-wrap! -mx-3!">
            <div class="w-full! shrink-0! grow-0! px-3!">

                <div class="p-10! max-md:p-6! max-sm:p-4! max-md:text-center!">

                    {{-- About Section --}}
                    <div class="[&_img]:max-w-[306px]! [&_img]:w-full! [&_img]:h-auto! [&_img]:mb-5! max-md:[&_img]:mx-auto! max-md:[&_img]:max-w-[220px]! [&_p]:text-[15px]! max-sm:[&_p]:text-[14px]! [&_p]:mb-0! [&_p]:text-[#f2f2f2]! [&_a]:text-white! [&_a]:underline! [&_a]:break-words!">
                        {!! \App\Services\ContentRenderer::render($about) !!}
                    </div>

                    {{-- Links Section --}}
                    <div class="flex! flex-row! flex-wrap! gap-x-5! gap-y-2! pt-5! pb-[30px]! mt-[30px]! max-md:flex-col! max-md:items-center! [&_a]:text-white! [&_a]:no-underline! [&_a]:text-sm! [&_a]:hover:underline!">
                        {!! \App\Services\ContentRenderer::render($links) !!}
                    </div>

                </div>

                {{-- Bottom Bar --}}
                <div class="border-t! border-white/20! text-center! p-5! max-sm:p-4! text-[15px]! max-sm:text-[13px]! text-[#f2f2f2]!">
                    {!! \App\Services\ContentRenderer::render($bottom) !!}
                </div>

            </div>
        </div>
    </div>
</section>
