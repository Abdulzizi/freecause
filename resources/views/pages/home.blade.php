@extends('layouts.legacy')

@section('title', 'FreeCause - Online Petition')

@php
    $featuredUrl = $featuredPetition
        ? lroute('petition.show', ['slug' => $featuredPetition->tr_slug, 'id' => $featuredPetition->id])
        : null;

    $signatures = $featuredPetition?->signature_count ?? 0;
    $goal = $featuredPetition?->goal_signatures ?? 100;
    $progress = $goal > 0 ? min(100, ($signatures / $goal) * 100) : 0;
@endphp

@section('content')

    <section id="welcomes" class="bg-[#f8f9fa]! pt-14! pb-16! md:pt-24! md:pb-20!">
        <x-ui.container>
            <div class="flex! flex-wrap! -mx-3! items-center! justify-center!">
                <div class="w-full! md:w-2/3! shrink-0! grow-0! px-3! text-center!">

                    <h1 class="text-black! font-semibold! text-2xl! md:text-5xl! mb-6! max-sm:mb-4!">
                        {{ $content['h1'] ?? __('home.h1') }}
                    </h1>

                    <h2 class="text-[#4b5563]! text-base! leading-[1.5]! md:text-xl! md:leading-[1.45]!">
                        {!! \App\Services\ContentRenderer::render($content['h2'] ?? __('home.h2')) !!}
                    </h2>

                    <div class="mt-7! mb-4!">
                        <x-ui.button :href="lroute('petition.create')" variant="primary" size="lg">
                            {{ __('home.btn_create_petition') }}
                        </x-ui.button>
                    </div>

                </div>
            </div>
        </x-ui.container>
    </section>

    <section class="bg-[#f9fafb]! py-14! md:py-24!">
        <x-ui.container>
            <div class="flex! flex-wrap! -mx-3!">
                <div class="w-full! shrink-0! grow-0! px-3!">
                    <div class="fcui-card">

                        <ul class="nav nav-tabs" id="myTab">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab"
                                    data-bs-target="#featured-petition-tab-pane">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                    {{ __('home.tab_featured') }}
                                </button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#recent-activities-tab-pane">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    {{ __('home.tab_recent') }}
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">

                            <div class="tab-pane fade show active" id="featured-petition-tab-pane">

                                <div class="p-4! md:p-[25px]! max-sm:p-3!">
                                    <div class="flex! flex-wrap! -mx-3!">
                                        <div class="w-full! shrink-0! grow-0! px-3! mb-[25px]! max-md:mb-[30px]!">

                                            <div class="featured-box relative! flex! flex-col! min-w-0! bg-white! bg-clip-border! rounded-md! shadow-none! transition! duration-150! hover:shadow-lg!">
                                                <div class="grow! shrink! basis-auto! p-5! max-sm:p-4!">

                                                    <span class="bg-[#ffefef]! text-danger! px-5! py-[3px]! rounded-[5px]! text-sm! mb-5! inline-block!">
                                                        {{ __('home.featured_badge') }}
                                                    </span>

                                                    <div class="flex! flex-wrap! -mx-3! relative!">

                                                        @if ($featuredPetition)
                                                        <div class="w-full! md:w-1/2! shrink-0! grow-0! px-3! mb-6! max-sm:mb-4!">
                                                            @php $wv = \App\Support\ImageVariants::urls($featuredPetition->cover_image); @endphp
                                                            @if ($wv)
                                                            <picture>
                                                                <source type="image/webp" srcset="{{ $wv['400'] }} 400w, {{ $wv['800'] }} 800w" sizes="(min-width: 768px) 50vw, 100vw">
                                                                <img src="{{ $featuredPetition->coverUrl() }}" class="fcui-blurup img-fluid w-full! aspect-[16/9]! object-cover!" data-blurup
                                                                    loading="eager" decoding="async" alt="{{ $featuredPetition->tr_title }}">
                                                            </picture>
                                                            @else
                                                            <img src="{{ $featuredPetition->coverUrl() }}" class="fcui-blurup img-fluid w-full! aspect-[16/9]! object-cover!" data-blurup
                                                                loading="eager" decoding="async" alt="{{ $featuredPetition->tr_title }}">
                                                            @endif
                                                        </div>

                                                        <div class="w-full! md:w-1/2! shrink-0! grow-0! px-3! mb-6! max-sm:mb-4!">
                                                                <h3>{{ $featuredPetition->tr_title }}</h3>
                                                                <p>
                                                                    <strong>
                                                                        {{ __('home.petition_target_label') }}
                                                                    </strong>
                                                                    {{ $featuredPetition->target ?? '-' }}
                                                                </p>

                                                                <p class="line-clamp-7!">
                                                                    {{ \Illuminate\Support\Str::limit(strip_tags($featuredPetition->tr_description ?? ''), 600) }}
                                                                </p>

                                                            @else
                                                                <h3>{{ __('home.featured_none_title') }}</h3>
                                                                <p class="text-[#6c757d]!">
                                                                    {{ __('home.featured_none_sub') }}
                                                                </p>
                                                            @endif
                                                        </div>

                                                        @if ($featuredPetition)
                                                            <a href="{{ $featuredUrl }}" data-prefetch class="absolute! inset-0! z-[2]! text-[0px]! bg-transparent!">
                                                                {{ __('home.featured_read_more') }}
                                                            </a>
                                                        @endif
                                                    </div>

                                                    @if ($featuredPetition)
                                                        <div class="mb-4!">
                                                            <div class="progress mb-2!">
                                                                <div class="progress-bar" style="width: {{ $progress }}%;"
                                                                    role="progressbar" aria-valuenow="{{ (int) $progress }}"
                                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                            <div class="flex! justify-between! text-[#6c757d]!">
                                                                <span><span data-countup data-countup-to="{{ (int) $signatures }}">{{ number_format($signatures) }}</span> {{ __('home.signatures_label') }}</span>
                                                                <span>{{ __('home.goal_label') }} {{ number_format($goal) }}</span>
                                                            </div>
                                                        </div>

                                                        <x-ui.button :href="$featuredUrl" variant="primary">
                                                            {{ __('home.featured_read_more') }}
                                                        </x-ui.button>
                                                    @endif

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane fade" id="recent-activities-tab-pane" role="tabpanel">

                                <div class="p-4! md:p-[25px]! max-sm:p-3!">
                                    <div class="flex! flex-wrap! -mx-3!">
                                        <div class="w-full! shrink-0! grow-0! px-3!">
                                            <div class="mb-[50px]!">

                                                <ul class="most-listing">

                                                    @forelse($recentActivities as $sig)
                                                        @php
                                                            $hasPetition =
                                                                !empty($sig->petition_id) &&
                                                                !empty($sig->petition_slug);

                                                            $url = $hasPetition
                                                                ? lroute('petition.show', [
                                                                    'slug' => $sig->petition_slug,
                                                                    'id' => $sig->petition_id,
                                                                ])
                                                                : '#';
                                                        @endphp

                                                        <li class="group">
                                                            <a href="{{ $url }}" @if($hasPetition) data-prefetch @endif>

                                                                <div
                                                                    class="flex! justify-between! mb-1!">
                                                                    <p class="text-sm!">
                                                                        <span>
                                                                            {{ $sig->name }}
                                                                        </span>

                                                                        <strong>
                                                                            {{ __('home.recent_has_signed') }}
                                                                        </strong>
                                                                    </p>

                                                                    <span class="text-brand! text-sm! group-hover:text-white!">
                                                                        {{ $sig->created_at ? \Carbon\Carbon::parse($sig->created_at)->diffForHumans() : '' }}
                                                                    </span>
                                                                </div>

                                                                <p class="text-sm!">
                                                                    {{ $sig->petition_title }}
                                                                </p>

                                                            </a>
                                                        </li>

                                                    @empty

                                                        <li class="group">
                                                            <div class="text-[#6c757d]! p-2!">
                                                                {{ __('home.recent_empty') }}
                                                                &mdash;
                                                                <a href="{{ lroute('petitions.index') }}" class="text-brand! group-hover:text-white!">
                                                                    {{ __('home.btn_browse_petitions') }}
                                                                </a>
                                                            </div>
                                                        </li>
                                                    @endforelse

                                                </ul>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </x-ui.container>
    </section>

    <section class="py-14! md:py-24!">
        <x-ui.container>
            <div class="flex! flex-wrap! -mx-3!">

                <div class="w-full! md:w-1/2! shrink-0! grow-0! px-3! mb-[25px]! max-md:mb-[30px]!">
                    <div class="gray-box bg-[#f9fafb]! p-[15px]! rounded-[10px]!">

                        <h2 class="capitalize! text-[1.3rem]! md:text-[1.5rem]! relative! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:w-full! after:left-0! after:bottom-0! after:bg-[linear-gradient(to_right,#000000_30%,#ce2020_30%)]!">
                            {{ __('home.what_title') }}
                        </h2>

                        {!! \App\Services\ContentRenderer::render($content['text_index_left'] ?? __('home.text_index_left')) !!}

                    </div>
                </div>

                <div class="w-full! md:w-1/2! shrink-0! grow-0! px-3!">
                    <div class="gray-box bg-[#f9fafb]! p-[15px]! rounded-[10px]!">

                        <h2 class="capitalize! text-[1.3rem]! md:text-[1.5rem]! relative! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:w-full! after:left-0! after:bottom-0! after:bg-[linear-gradient(to_right,#000000_30%,#ce2020_30%)]!">
                            {{ __('home.create_box_title') }}
                        </h2>

                        {!! \App\Services\ContentRenderer::render($content['text_index_right'] ?? __('home.text_index_right')) !!}

                    </div>
                </div>

            </div>
        </x-ui.container>
    </section>

    <section class="py-14! md:py-24!">
        <x-ui.container>
            <div class="flex! flex-wrap! -mx-3! mb-6!">
                <div class="w-full! shrink-0! grow-0! px-3!">
                    <h2 class="capitalize! text-[1.3rem]! md:text-[1.5rem]! relative! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:w-full! after:left-0! after:bottom-0! after:bg-[linear-gradient(to_right,#000000_30%,#ce2020_30%)]!">{{ __('home.categories_title') }}</h2>
                </div>
            </div>

            <div class="flex! flex-wrap! -mx-3!">
                @foreach ($categories as $cat)
                    <div class="w-full! lg:w-1/4! sm:w-1/2! shrink-0! grow-0! px-3! mb-4!">
                        <a href="{{ lroute('petitions.byCategory', ['categorySlug' => $cat->tr_slug, 'category' => $cat->id]) }}"
                            class="bg-white! border! border-border! rounded-lg! p-6! text-center! no-underline! text-black! transition-all! duration-200! hover:shadow-[0_2px_8px_rgba(0,0,0,0.1)]! block!">
                            <span class="text-[25px]! mb-2! hidden!"><i class="bi {{ \App\Support\CategoryIcons::get($cat->id) }}"></i></span>
                            <h3 class="text-base! md:text-base!">{{ $cat->tr_name }}</h3>
                        </a>
                    </div>
                @endforeach
            </div>
        </x-ui.container>
    </section>

    @if (count($magazinePosts) > 0)
    <section class="bg-[#f9fafb]! py-14! md:py-24!">
        <x-ui.container>
            <div class="text-center! mb-6!">
                <h2 class="mb-2!">{{ __('home.blog_title') }}</h2>
                <p class="text-[#6c757d]!">{{ __('home.blog_subtitle') }}
                </p>
            </div>

            <div class="flex! flex-wrap! -mx-3!">
                @foreach ($magazinePosts as $post)
                    <div class="w-full! md:w-1/3! shrink-0! grow-0! px-3! mb-6!">
                        <a class="block! h-full! no-underline!" href="/magazine/{{ $post->post_name }}">
                            <div class="shadow-[var(--fc-shadow-sm)]! rounded-[15px]! overflow-hidden! transition! duration-150! hover:-translate-y-0.5! hover:shadow-[var(--fc-shadow-md)]!">
                                <div>
                                    @if ($post->thumbnail_path)
                                        <img src="/magazine/wp-content/uploads/{{ $post->thumbnail_path }}" alt="{{ $post->post_title }}" loading="lazy" decoding="async" data-blurup class="fcui-blurup w-full! aspect-[16/9]! object-cover!">
                                    @else
                                        <img src="/magazine/wp-content/themes/freecause/assets/images/default-post.jpg" alt="{{ $post->post_title }}" loading="lazy" decoding="async" data-blurup onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22250%22%3E%3Crect fill=%22%23e9ecef%22 width=%22400%22 height=%22250%22/%3E%3Ctext fill=%22%236c757d%22 font-family=%22sans-serif%22 font-size=%2218%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E'" class="fcui-blurup w-full! aspect-[16/9]! object-cover!">
                                    @endif
                                </div>
                                <div class="p-5! bg-white! min-h-[146px]!">
                                    <h3 class="text-base! md:text-base! mb-2.5! text-black!">{{ $post->post_title }}</h3>
                                    <span class="text-brand!">
                                    {{ __('home.blog_read_more') }}
                                    <span class="ml-1.5!">›</span>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="text-center! mt-6!">
                <x-ui.button variant="ghost" href="/magazine">
                    {{ __('home.blog_view_all') }}
                </x-ui.button>
            </div>
        </x-ui.container>
    </section>
    @endif
@endsection

@push('head')
    @include('partials.hreflang')
@endpush
