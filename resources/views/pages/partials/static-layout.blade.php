<section class="bg-[#fbfbfb]! py-10! md:py-16!">
    <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">
        <div class="flex! flex-wrap! -mx-3!">
            <div class="w-full! shrink-0! grow-0! px-3!">

                <h1 class="text-2xl! md:text-3xl! font-semibold! mb-1!">{{ $title }}</h1>

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0! p-0!">
                        <li class="breadcrumb-item">
                            <a href="{{ lroute('home') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $title }}
                        </li>
                    </ol>
                </nav>

            </div>
        </div>
    </div>
</section>

<section class="petitions-list py-14! md:py-24!">
    <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">
        <div class="flex! flex-wrap! -mx-3!">
            <div class="w-full! shrink-0! grow-0! px-3!">

                <div class="fcui-card relative! flex! flex-col! min-w-0!">
                    <div class="grow! shrink! basis-auto! p-6!">

                        @if (($fallback ?? false))
                            <div class="rounded-md! border! border-amber-300! bg-amber-50! px-4! py-3! text-[13px]! text-amber-900! mb-4!" role="status">
                                {{ __('pages.lang_fallback_notice') }}
                            </div>
                        @endif

                        <h2 class="relative! capitalize! text-[18px]! pb-[10px]! mb-[20px]! font-semibold! after:absolute! after:content-['']! after:h-[1px]! after:w-full! after:left-0! after:bottom-0! after:bg-gradient-to-r! after:from-black! after:from-30%! after:to-[#ce2020]!">{{ $title }}</h2>

                        <div class="fcui-prose [&_h1]:text-[18px]! [&_h1]:mt-[30px]! [&_h1]:font-semibold! [&_h2]:text-[18px]! [&_h2]:mt-[30px]! [&_h2]:font-semibold! [&_h3]:text-[18px]! [&_h3]:mt-[30px]! [&_h3]:font-semibold! [&_ul]:pl-[20px]! [&_ul]:mb-[15px]!">
                            {!! \App\Services\ContentRenderer::render($content) !!}
                        </div>

                        <x-ad-unit placement="article_footer" class="mt-6!" />

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
