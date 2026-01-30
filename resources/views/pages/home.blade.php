@extends('layouts.legacy')

@section('title', 'Freecause - Online Petition')

@php
    $picClass = 'pic' . random_int(0, 13);

    $h1 = 'Change the World';
    $h2 =
        'Welcome to <span class="red">FreeCause - Online Petition</span>, the ultimate spot to kick off your online petition—let’s make some waves!';
@endphp

@php
    $featuredUrl = $featuredPetition
        ? lroute('petition.show', ['slug' => $featuredPetition->slug, 'id' => $featuredPetition->id])
        : lroute('petitions.index');

    $signatures = $featuredPetition?->signature_count ?? 0;
    $goal = $featuredPetition?->goal_signatures ?? 100;
    $progress = $goal > 0 ? min(100, ($signatures / $goal) * 100) : 0;
@endphp

@php
    $demoPetitionUrl = url('/' . app()->getLocale() . '/petition/stop-using-plastics-in-our-oceans/75241');
@endphp

@section('content')
    <section id="welcomes" class="main-banner {{ $picClass }}">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-md-8 text-center">
                    <div class="banner-content">
                        <h1 class="banner-heading mb-4">{{ $h1 }}</h1>

                        <h2 class="banner-subtitle">{!! $h2 !!}</h2>

                        <div class="banner-btn mb-3">
                            <a href="{{ url(app()->getLocale() . '/petitions/demo-petition') }}"
                                class="btn btn-primary banner-btn-links">
                                Create Petition
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
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="featured-petition-tab" data-bs-toggle="tab"
                                    data-bs-target="#featured-petition-tab-pane" type="button" role="tab">
                                    <i class="fa fa-home" aria-hidden="true"></i> Featured Petition
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="recent-activities-tab" data-bs-toggle="tab"
                                    data-bs-target="#recent-activities-tab-pane" type="button" role="tab">
                                    <i class="fa fa-clock-o" aria-hidden="true"></i> Recent activities
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            {{-- featured --}}
                            <div class="tab-pane fade show active" id="featured-petition-tab-pane" role="tabpanel">
                                <div class="tab-card-content">
                                    <div class="row featured-row">
                                        <div class="col-lg-12 mb-25">
                                            <div class="card featured-box">
                                                <div class="card-body">
                                                    <span class="featured-badges">Featured Petition</span>

                                                    <div class="row featured-box-inner">
                                                        <div class="col-sm-6 mb-4">
                                                            <img src="{{ $featuredPetition?->coverUrl() }}"
                                                                class="img-fluid" alt="Featured" />
                                                        </div>

                                                        <div class="col-sm-6 mb-4">
                                                            @if ($featuredPetition)
                                                                <h5>{{ $featuredPetition->title }}</h5>

                                                                <p>
                                                                    <strong>Petition target:</strong>
                                                                    {{ $featuredPetition->target ?? '-' }}
                                                                </p>

                                                                <p>
                                                                    {{ \Illuminate\Support\Str::limit($featuredPetition->description ?? '', 600) }}
                                                                </p>
                                                            @else
                                                                <h5>no featured petition yet</h5>
                                                                <p class="text-muted mb-0">no petitions yet for this locale
                                                                </p>
                                                            @endif
                                                        </div>

                                                        <a href="{{ $featuredUrl }}">read more</a>
                                                    </div>

                                                    <div class="goal-progress mb-3">
                                                        <div class="progress mb-2">
                                                            <div class="progress-bar" style="width: {{ $progress }}%;">
                                                            </div>
                                                        </div>
                                                        <div class="d-flex justify-content-between text-muted">
                                                            <span>{{ number_format($signatures) }} signatures</span>
                                                            <span>Goal: {{ number_format($goal) }}</span>
                                                        </div>
                                                    </div>

                                                    <a href="{{ $featuredUrl }}" class="btn btn-danger">read more</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- recent activities --}}
                            <div class="tab-pane fade" id="recent-activities-tab-pane" role="tabpanel">
                                <div class="tab-card-content">
                                    <div class="row recent-activities-row">
                                        <div class="col-md-12">
                                            <div class="most-grid">
                                                <ul class="recent-activities most-listing">
                                                    @forelse($recentActivities as $sig)
                                                        @php
                                                            $p = $sig->petition;
                                                            $url = $p
                                                                ? lroute('petition.show', [
                                                                    'slug' => $p->slug,
                                                                    'id' => $p->id,
                                                                ])
                                                                : '#';

                                                            $name = trim((string) ($sig->name ?? 'someone'));
                                                            $nameParts = preg_split(
                                                                '/\s+/',
                                                                $name,
                                                                -1,
                                                                PREG_SPLIT_NO_EMPTY,
                                                            );
                                                            $prettyName =
                                                                count($nameParts) >= 2
                                                                    ? $nameParts[0] .
                                                                        ' ' .
                                                                        strtoupper(mb_substr($nameParts[1], 0, 1)) .
                                                                        '.'
                                                                    : $name;
                                                        @endphp

                                                        <li>
                                                            <a href="{{ $url }}">
                                                                <div
                                                                    class="d-flex justify-content-between recent-activities-times mb-1">
                                                                    <p class="fs-14">
                                                                        <span class="user-name">{{ $prettyName }}</span>
                                                                        <strong>has signed</strong>
                                                                    </p>
                                                                    <span
                                                                        class="red fs-14">{{ optional($sig->created_at)->diffForHumans() }}</span>
                                                                </div>

                                                                <p class="fs-14">{{ $p?->title ?? 'petition' }}</p>
                                                            </a>
                                                        </li>
                                                    @empty
                                                        <li>
                                                            <div class="text-muted p-2">no recent activity yet</div>
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
                        <h4 class="headings">What is online petition</h4>
                        <div class="mb-4">
                            <p>Welcome to <span class="red">FreeCause - Online Petition</span>, the ultimate platform
                                for launching your online petitions. Champion your cause and make your voice heard!</p>

                            <p>At <span class="red">FreeCause - Online Petition</span>, we believe real change starts
                                with individuals like you—bold enough to share your ideas and inspire others to take action.
                            </p>

                            <p>Without a space to champion our causes, no matter how small or everyday they may seem, true
                                freedom feels out of reach.</p>

                            <p>That’s why we built <span class="red">FreeCause - Online Petition</span>—free,
                                independent, and made for you.</p>

                            <p><a href="#">Learn how to start your petition »</a></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="gray-box">
                        <h4 class="headings">CREATE PETITION</h4>
                        <div class="mb-4">
                            <p>Supercharge your cause!</p>
                            <ul>
                                <li> - The #1 platform to gather signatures</li>
                                <li> - Always free to use, no strings attached</li>
                                <li> - Easily share your petition across all social platforms</li>
                                <li> - Download signatures in PDF or DOC format—perfect for printing or delivering in person
                                </li>
                                <li> - Get maximum visibility to boost your impact</li>
                                <li> - Ethical Code</li>
                            </ul>
                            <p>Let’s build change together from the ground up!</p>
                            <p><a href="#">Launch your first petition now »</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="browse-categories py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4 class="headings">Browse categories</h4>
                </div>
            </div>

            <div class="row category-row">
                @foreach ($categories as $cat)
                    <div class="col-lg-3 col-sm-6 mb-3">
                        <a href="{{ url(app()->getLocale() . '/petitions/' . 'category-' . $cat->slug . '-' . $cat->id) }}"
                            class="category-card d-block">
                            <span class="category-icon"><i class="bi bi-house-check"></i></span>
                            <h3 class="h5">{{ $cat->name }}</h3>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="blog-section">
        <div class="container">
            <div class="text-center mb-4">
                <h2 class="mb-2">Latest from Freecause magazine</h2>
                <p class="text-muted">Stay updated with our latest insights and news</p>
            </div>

            <div class="row g-4">
                @foreach ([['img' => asset('legacy/images/demo-mag-1.avif'), 'title' => 'Mongolia Death Penalty Ban: How Amnesty International Changed Law'], ['img' => asset('legacy/images/demo-mag-2.jpg'), 'title' => 'Overcoming Apathy Inspiring People to Take the First Step'], ['img' => asset('legacy/images/demo-mag-3.jpeg'), 'title' => 'Why Authenticity Is Key To Petition Success']] as $post)
                    <div class="col-md-4">
                        <div class="blog-grid">
                            <div class="blog-images">
                                <img src="{{ $post['img'] }}" alt="">
                            </div>
                            <div class="blog-content">
                                <h3 class="blog-title">{{ $post['title'] }}</h3>
                                <a class="red" href="#">Read More <span style="margin-left:6px;">›</span></a>
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
