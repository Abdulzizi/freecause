@extends('layouts.legacy')

@section('title', 'xPetition - Online Petition')

@php
    $featuredUrl = $featuredPetition
        ? lroute('petition.show', ['slug' => $featuredPetition->tr_slug, 'id' => $featuredPetition->id])
        : lroute('petitions.index');

    $signatures = $featuredPetition?->signature_count ?? 0;
    $goal = $featuredPetition?->goal_signatures ?? 100;
    $progress = $goal > 0 ? min(100, ($signatures / $goal) * 100) : 0;
@endphp

@section('content')

    <section id="welcomes" class="main-banner">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-md-8 text-center">
                    <div class="banner-content">

                        <h1 class="banner-heading mb-4">
                            {{ $content['h1'] ?? 'Change the World' }}
                        </h1>

                        <h2 class="banner-subtitle">
                            {!! $content['h2'] ?? '' !!}
                        </h2>

                        <div class="banner-btn mb-3">
                            <a href="{{ lroute('petition.create') }}" class="btn btn-primary banner-btn-links">
                                {{ $content['btn_create_petition'] ?? 'Create Petition' }}
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="tabs-area">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="tab-cards">

                        <ul class="nav nav-tabs" id="myTab">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab"
                                    data-bs-target="#featured-petition-tab-pane">
                                    <i class="fa fa-home"></i>
                                    {{ $content['tab_featured'] ?? 'Featured Petition' }}
                                </button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#recent-activities-tab-pane">
                                    <i class="fa fa-clock-o"></i>
                                    {{ $content['tab_recent'] ?? 'Recent activities' }}
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">

                            <div class="tab-pane fade show active" id="featured-petition-tab-pane">

                                <div class="tab-card-content">
                                    <div class="row featured-row">
                                        <div class="col-lg-12 mb-25">

                                            <div class="card featured-box position-relative">
                                                <div class="card-body">

                                                    <div class="row featured-box-inner">

                                                        @if ($featuredPetition)
                                                        <div class="col-sm-6 mb-4">
                                                            <span class="featured-badges">
                                                                {{ $content['featured_badge'] ?? 'Featured Petition' }}
                                                            </span>
                                                            <img src="{{ $featuredPetition->coverUrl() }}"
                                                                class="img-fluid w-100"
                                                                loading="lazy"
                                                                style="max-height:320px;object-fit:cover;border-radius:6px;" />
                                                        </div>

                                                        <div class="col-sm-6 mb-4">
                                                                <h5>{{ $featuredPetition->tr_title }}</h5>
                                                                <p>
                                                                    <strong>
                                                                        {{ $content['petition_target_label'] ?? 'Petition target:' }}
                                                                    </strong>
                                                                    {{ $featuredPetition->target ?? '-' }}
                                                                </p>

                                                                <p>
                                                                    {{ \Illuminate\Support\Str::limit(strip_tags($featuredPetition->tr_description ?? ''), 600) }}
                                                                </p>

                                                                <div class="goal-progress mb-3">
                                                                    <div class="progress mb-2">
                                                                        <div class="progress-bar"
                                                                            style="width: {{ $progress }}%;"></div>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between text-muted">
                                                                        <span>
                                                                            {{ number_format($signatures) }}
                                                                            {{ $content['signatures_label'] ?? 'signatures' }}
                                                                        </span>
                                                                        <span>
                                                                            {{ $content['goal_label'] ?? 'Goal:' }}
                                                                            {{ number_format($goal) }}
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                <a href="{{ $featuredUrl }}" class="btn btn-danger stretched-link">
                                                                    {{ $content['read_more'] ?? 'read more' }}
                                                                </a>
                                                            @else
                                                                <h5>{{ $content['featured_none_title'] ?? 'no featured petition yet' }}</h5>
                                                                <p class="text-muted">
                                                                    {{ $content['featured_none_sub'] ?? '' }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane fade" id="recent-activities-tab-pane" role="tabpanel">

                                <div class="tab-card-content">
                                    <div class="row recent-activities-row">
                                        <div class="col-md-12">
                                            <div class="most-grid">

                                                <ul class="recent-activities most-listing">

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

                                                        <li>
                                                            <a href="{{ $url }}">

                                                                <div
                                                                    class="d-flex justify-content-between recent-activities-times mb-1">
                                                                    <p class="fs-14">
                                                                        <span class="user-name">
                                                                            {{ $sig->name }}
                                                                        </span>

                                                                        <strong>
                                                                            {{ $content['recent_has_signed'] ?? 'has signed' }}
                                                                        </strong>
                                                                    </p>

                                                                    <span class="red fs-14">
                                                                        {{ $sig->created_at ? \Carbon\Carbon::parse($sig->created_at)->diffForHumans() : '' }}
                                                                    </span>
                                                                </div>

                                                                <p class="fs-14">
                                                                    {{ $sig->petition_title }}
                                                                </p>

                                                            </a>
                                                        </li>

                                                    @empty

                                                        <li>
                                                            <div class="text-muted p-2">
                                                                {{ $content['recent_empty'] ?? 'no recent activity yet' }}
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

    <section class="online-petition py-5">
        <div class="container">
            <div class="row">

                <div class="col-md-6 mb-25">
                    <div class="gray-box">

                        <h4 class="headings">
                            {{ $content['what_title'] ?? 'What is online petition' }}
                        </h4>

                        {!! \App\Services\ContentRenderer::render($content['text_index_left'] ?? '') !!}

                    </div>
                </div>

                <div class="col-md-6">
                    <div class="gray-box">

                        <h4 class="headings">
                            {{ $content['create_box_title'] ?? 'CREATE PETITION' }}
                        </h4>

                        {!! \App\Services\ContentRenderer::render($content['text_index_right'] ?? '') !!}

                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="browse-categories py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4 class="headings">{{ $content['categories_title'] ?? 'Browse categories' }}</h4>
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

            <div class="row category-row">
                @foreach ($categories as $cat)
                    <div class="col-lg-3 col-sm-6 mb-3">
                        <a href="{{ lroute('petitions.byCategory', ['categorySlug' => $cat->tr_slug, 'category' => $cat->id]) }}"
                            class="category-card d-block">
                            <span class="category-icon"><i class="bi {{ $categoryIcons[$cat->id] ?? 'bi-collection' }}"></i></span>
                            <h3 class="h5">{{ $cat->tr_name }}</h3>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="blog-section">
        <div class="container">
            <div class="text-center mb-4">
                <h2 class="mb-2">{{ $content['blog_title'] ?? 'Latest from xPetition magazine' }}</h2>
                <p class="text-muted">{{ $content['blog_subtitle'] ?? 'Stay updated with our latest insights and news' }}
                </p>
            </div>

            <div class="row g-4">
                @foreach ($magazinePosts as $post)
                    <div class="col-md-4">
                        <div class="blog-grid">
                            <div class="blog-images">
                                @if ($post->thumbnail_path)
                                    <img src="/magazine/wp-content/uploads/{{ $post->thumbnail_path }}" alt="{{ $post->post_title }}">
                                @endif
                            </div>
                            <div class="blog-content">
                                <h3 class="blog-title">{{ $post->post_title }}</h3>
                                <a class="red" href="/magazine/{{ $post->post_name }}">
                                    {{ $content['blog_read_more'] ?? 'Read More' }}
                                    <span style="margin-left:6px;">›</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $("#slider .row a").hover(
                function() {
                    $(this).find(".popup").slideDown("fast");
                },
                function() {
                    $(this).find(".popup").slideUp("fast");
                }
            );
        });
    </script>
@endpush