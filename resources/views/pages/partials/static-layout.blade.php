<section class="bg-[#fbfbfb]! py-[30px]!">
    <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">
        <div class="flex! flex-wrap! -mx-3!">
            <div class="w-full! shrink-0! grow-0! px-3!">

                <h1 class="text-[20px]! mb-[5px]!">{{ $title }}</h1>

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

<section class="petitions-list py-12!">
    <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">
        <div class="flex! flex-wrap! -mx-3!">
            <div class="w-full! shrink-0! grow-0! px-3!">

                @if (($fallback ?? false))
                    <div class="rounded-md! border! border-amber-300! bg-amber-50! px-4! py-3! text-[13px]! text-amber-900! mb-4!" role="status">
                        {{ __('pages.lang_fallback_notice') }}
                    </div>
                @endif

                <div class="[&_h1]:text-[18px]! [&_h1]:mt-[30px]! [&_h1]:font-semibold! [&_h2]:text-[18px]! [&_h2]:mt-[30px]! [&_h2]:font-semibold! [&_h3]:text-[18px]! [&_h3]:mt-[30px]! [&_h3]:font-semibold! [&_ul]:pl-[20px]! [&_ul]:mb-[15px]!">
                    {!! \App\Services\ContentRenderer::render($content) !!}
                </div>

                <x-ad-unit placement="article_footer" class="mt-6!" />

            </div>
        </div>
    </div>
</section>
