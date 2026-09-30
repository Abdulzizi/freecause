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

    <section id="welcomes" class="bg-[#f8f9fa]! pt-20! pb-20! md:pt-12! md:pb-12!">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-md-8 text-center">

                    <h1 class="text-black! font-semibold! text-2xl! md:text-5xl! mb-6! max-sm:mb-4!">
                        {{ $content['h1'] ?? __('home.h1') }}
                    </h1>

                    <h2 class="text-[#4b5563]! text-base! leading-[1.5]! md:text-xl! md:leading-[1.2]!">
                        {!! \App\Services\ContentRenderer::render($content['h2'] ?? __('home.h2')) !!}
                    </h2>

                    <div class="mb-3">
                        <a href="{{ lroute('petition.create') }}" class="btn btn-primary text-lg! font-medium! inline-block! px-6! py-2.5!">
                            {{ __('home.btn_create_petition') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <section class="bg-[#f9fafb]! py-15! md:py-6!">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="shadow-[0_1px_2px_0_rgba(0,0,0,0.05)]! bg-white! rounded-[10px]!">

                        <ul class="nav nav-tabs" id="myTab">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab"
                                    data-bs-target="#featured-petition-tab-pane">
                                    <i class="fa fa-home"></i>
                                    {{ __('home.tab_featured') }}
                                </button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#recent-activities-tab-pane">
                                    <i class="fa fa-clock-o"></i>
                                    {{ __('home.tab_recent') }}
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">

                            <div class="tab-pane fade show active" id="featured-petition-tab-pane">

                                <div class="p-4! md:p-[25px]! max-sm:p-3!">
                                    <div class="row">
                                        <div class="col-lg-12 mb-[25px]! max-md:mb-[30px]!">

                                            <div class="card featured-box relative! shadow-none!">
                                                <div class="card-body p-0!">

                                                    <span class="bg-[#ffefef]! text-[#dc3545]! px-5! py-[3px]! rounded-[5px]! text-sm! mb-5! inline-block!">
                                                        {{ __('home.featured_badge') }}
                                                    </span>

                                                    <div class="row relative!">

                                                        @if ($featuredPetition)
                                                        <div class="col-md-6 mb-6! max-sm:mb-4!">
                                                            <img src="{{ $featuredPetition->coverUrl() }}" class="img-fluid"
                                                                loading="lazy" alt="{{ $featuredPetition->tr_title }}">
                                                        </div>

                                                        <div class="col-md-6 mb-6! max-sm:mb-4!">
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
                                                                <p class="text-muted">
                                                                    {{ __('home.featured_none_sub') }}
                                                                </p>
                                                            @endif
                                                        </div>

                                                        @if ($featuredPetition)
                                                            <a href="{{ $featuredUrl }}" class="absolute! inset-0! z-[2]! text-[0px]! bg-transparent!">
                                                                {{ __('home.featured_read_more') }}
                                                            </a>
                                                        @endif
                                                    </div>

                                                    @if ($featuredPetition)
                                                        <div class="mb-3">
                                                            <div class="progress mb-2">
                                                                <div class="progress-bar" style="width: {{ $progress }}%;"></div>
                                                            </div>
                                                            <div class="d-flex justify-content-between text-muted">
                                                                <span>{{ number_format($signatures) }} {{ __('home.signatures_label') }}</span>
                                                                <span>{{ __('home.goal_label') }} {{ number_format($goal) }}</span>
                                                            </div>
                                                        </div>

                                                        <a href="{{ $featuredUrl }}" class="btn btn-primary">
                                                            {{ __('home.featured_read_more') }}
                                                        </a>
                                                    @endif

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane fade" id="recent-activities-tab-pane" role="tabpanel">

                                <div class="p-4! md:p-[25px]! max-sm:p-3!">
                                    <div class="row">
                                        <div class="col-md-12">
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
                                                            <a href="{{ $url }}">

                                                                <div
                                                                    class="d-flex justify-content-between mb-1">
                                                                    <p class="text-sm!">
                                                                        <span>
                                                                            {{ $sig->name }}
                                                                        </span>

                                                                        <strong>
                                                                            {{ __('home.recent_has_signed') }}
                                                                        </strong>
                                                                    </p>

                                                                    <span class="text-[#cc1f1f]! text-sm! group-hover:text-white!">
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
                                                            <div class="text-muted p-2">
                                                                {{ __('home.recent_empty') }}
                                                                &mdash;
                                                                <a href="{{ lroute('petitions.index') }}" class="text-[#cc1f1f]! group-hover:text-white!">
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
        </div>
    </section>

    <section class="py-16!">
        <div class="container">
            <div class="row">

                <div class="col-md-6 mb-[25px]! max-md:mb-[30px]!">
                    <div class="gray-box bg-[#f9fafb]! p-[15px]! rounded-[10px]!">

                        <h2 class="capitalize! text-[1.3rem]! md:text-lg! relative! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:w-full! after:left-0! after:bottom-0! after:bg-[linear-gradient(to_right,#000000_30%,#ce2020_30%)]!">
                            {{ __('home.what_title') }}
                        </h4>

                        {!! \App\Services\ContentRenderer::render($content['text_index_left'] ?? __('home.text_index_left')) !!}

                    </div>
                </div>

                <div class="col-md-6">
                    <div class="gray-box bg-[#f9fafb]! p-[15px]! rounded-[10px]!">

                        <h2 class="capitalize! text-[1.3rem]! md:text-lg! relative! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:w-full! after:left-0! after:bottom-0! after:bg-[linear-gradient(to_right,#000000_30%,#ce2020_30%)]!">
                            {{ __('home.create_box_title') }}
                        </h4>

                        {!! \App\Services\ContentRenderer::render($content['text_index_right'] ?? __('home.text_index_right')) !!}

                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="py-16!">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-12">
                    <h2 class="capitalize! text-[1.3rem]! md:text-lg! relative! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:w-full! after:left-0! after:bottom-0! after:bg-[linear-gradient(to_right,#000000_30%,#ce2020_30%)]!">{{ __('home.categories_title') }}</h2>
                </div>
            </div>

            @php
                $categoryIcons = [
                    1  => 'bi-hearts',
                    2  => 'bi-briefcase',
                    3  => 'bi-building',
                    4  => 'bi-palette',
                    5  => 'bi-book',
                    6  => 'bi-tree',
                    7  => 'bi-heart-pulse',
                    8  => 'bi-person-check',
                    9  => 'bi-flag',
                    10 => 'bi-cpu',
                    11 => 'bi-trophy',
                    12 => 'bi-airplane',
                    13 => 'bi-car-front',
                    14 => 'bi-people',
                    15 => 'bi-house-heart',
                ];
            @endphp

            <div class="row">
                @foreach ($categories as $cat)
                    <div class="col-lg-3 col-sm-6 mb-3">
                        <a href="{{ lroute('petitions.byCategory', ['categorySlug' => $cat->tr_slug, 'category' => $cat->id]) }}"
                            class="bg-white! border border-[#eee]! rounded-lg! p-6! text-center! no-underline! text-black! transition-all! duration-200! hover:shadow-[0_2px_8px_rgba(0,0,0,0.1)]! d-block">
                            <span class="text-[25px]! mb-2! hidden!"><i class="bi {{ $categoryIcons[$cat->id] ?? 'bi-collection' }}"></i></span>
                            <h4 class="h5 text-base! md:text-base!">{{ $cat->tr_name }}</h4>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if (count($magazinePosts) > 0)
    <section class="bg-[#f9fafb]! py-15!">
        <div class="container">
            <div class="text-center mb-4">
                <h2 class="mb-2">{{ __('home.blog_title') }}</h2>
                <p class="text-muted">{{ __('home.blog_subtitle') }}
                </p>
            </div>

            <div class="row g-4">
                @foreach ($magazinePosts as $post)
                    <div class="col-md-4">
                        <a class="d-block h-100 text-decoration-none" href="/magazine/{{ $post->post_name }}">
                            <div class="shadow-[0_1px_2px_0_rgba(0,0,0,0.05)]! rounded-[15px]! overflow-hidden! hover:shadow-[rgba(149,157,165,0.2)_0px_8px_24px]!">
                                <div>
                                    @if ($post->thumbnail_path)
                                        <img src="/magazine/wp-content/uploads/{{ $post->thumbnail_path }}" alt="{{ $post->post_title }}" class="h-auto! w-auto! object-cover! md:h-[220px]! md:w-full!">
                                    @else
                                        <img src="/magazine/wp-content/themes/freecause/assets/images/default-post.jpg" alt="{{ $post->post_title }}" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22250%22%3E%3Crect fill=%22%23e9ecef%22 width=%22400%22 height=%22250%22/%3E%3Ctext fill=%22%236c757d%22 font-family=%22sans-serif%22 font-size=%2218%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E'" class="h-auto! w-auto! object-cover! md:h-[220px]! md:w-full!">
                                    @endif
                                </div>
                                <div class="p-5! bg-white! min-h-[146px]!">
                                    <h4 class="text-base! md:text-base! mb-2.5! text-black!">{{ $post->post_title }}</h4>
                                    <span class="text-[#cc1f1f]!">
                                    {{ __('home.blog_read_more') }}
                                    <span class="ml-1.5!">›</span>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-4">
                <a href="/magazine" class="btn btn-outline-danger">
                    {{ __('home.blog_view_all') }}
                </a>
            </div>
        </div>
    </section>
    @endif
@endsection
