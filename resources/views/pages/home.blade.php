@extends('layouts.legacy')

@section('title', 'Freecause - Online Petition')

@php
    // static random class like production (pic0..pic13)
    $picClass = 'pic' . random_int(0, 13);

    // milestone static text
    $h1 = 'Change the World';
    $h2 =
        'Welcome to <span class="red">FreeCause - Online Petition</span>, the ultimate spot to kick off your online petition—let’s make some waves!';
@endphp

@php
  $demoPetitionUrl = url('/' . app()->getLocale() . '/petition/stop-using-plastics-in-our-oceans/75241');
@endphp

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

                        {{-- static country ui (no backend yet) --}}
                        <p class="current-country d-flex align-items-center">
                            <i class="fa fa-globe pe-2 red fs-20"></i>
                            <span class="pe-2">Current country :</span>
                            <strong><a href="#" id="countrybutton">United States</a></strong>
                        </p>

                        <div id="boxcountries" style="display:none;">
                            <ul>
                                <li class="selected"><a href="#">United States</a></li>
                                <li><a href="#">France</a></li>
                                <li><a href="#">Italy</a></li>
                            </ul>
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
                            {{-- Featured --}}
                            <div class="tab-pane fade show active" id="featured-petition-tab-pane" role="tabpanel">
                                <div class="tab-card-content">
                                    <div class="row featured-row">
                                        <div class="col-lg-12 mb-25">
                                            <div class="card featured-box">
                                                <div class="card-body">
                                                    <span class="featured-badges">Featured Petition</span>

                                                    <div class="row featured-box-inner">
                                                        <div class="col-sm-6 mb-4">
                                                            <img src="{{ asset('legacy/images/demo-featured.jpg') }}"
                                                                class="img-fluid" alt="Featured" />
                                                        </div>
                                                        <div class="col-sm-6 mb-4">
                                                            <h5>End Child Labor: Enforce Stronger Global Regulations</h5>
                                                            <p><strong>Petition target:</strong> Government of India</p>
                                                            <p>Child labor is one of the greatest human rights violations of
                                                                our time. Millions of children around the world are forced
                                                                to work in hazardous conditions, depriving them of their
                                                                childhood, education, and basic rights. This petition calls
                                                                on the Government of India to enforce stricter regulations
                                                                and ensure that children are protected from exploitation.
                                                            </p>
                                                            <p>By signing this petition, you are taking a stand against
                                                                child labor and advocating for a brighter future for
                                                                children everywhere. Together, we can push for stronger
                                                                laws, better enforcement, and increased awareness to end
                                                                this injustice once and for all.</p>
                                                        </div>
                                                        {{-- <a href="{{ url(app()->getLocale() . '/petitions/demo-petition') }}">read more</a> --}}
                                                    <a href="{{ $demoPetitionUrl }}">read more</a>
                                                    </div>

                                                    @php
                                                        $signatures = 12500;
                                                        $goal = 25000;
                                                        $progress = ($signatures / $goal) * 100;
                                                    @endphp

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

                                                    <a href="{{ $demoPetitionUrl }}" class="btn btn-danger">read more</a>
                                                    {{-- <a href="{{ url(app()->getLocale() . '/petitions/demo-petition') }}" class="btn btn-primary">read more</a> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            {{-- Recent activities --}}
                            <div class="tab-pane fade" id="recent-activities-tab-pane" role="tabpanel">
                                <div class="tab-card-content">
                                    <div class="row recent-activities-row">
                                        <div class="col-md-12">
                                            <div class="most-grid">
                                                <ul class="recent-activities most-listing">
                                                    <li>
                                                        <a href="#">
                                                            <div
                                                                class="d-flex justify-content-between recent-activities-times mb-1">
                                                                <p class="fs-14"><span class="user-name">John D.</span>
                                                                    <strong>has signed</strong>
                                                                </p>
                                                                <span class="red fs-14">2 hours ago</span>
                                                            </div>
                                                            <p class="fs-14">End Child Labor: Enforce Stronger Global
                                                                Regulations</p>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div
                                                                class="d-flex justify-content-between recent-activities-times mb-1">
                                                                <p class="fs-14"><span class="user-name">Maria S.</span>
                                                                    <strong>has signed</strong>
                                                                </p>
                                                                <span class="red fs-14">Yesterday</span>
                                                            </div>
                                                            <p class="fs-14">Protect Local Wildlife Habitats</p>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div
                                                                class="d-flex justify-content-between recent-activities-times mb-1">
                                                                <p class="fs-14"><span class="user-name">Alex T.</span>
                                                                    <strong>has signed</strong>
                                                                </p>
                                                                <span class="red fs-14">3 days ago</span>
                                                            </div>
                                                            <p class="fs-14">Support Renewable Energy Initiatives</p>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div
                                                                class="d-flex justify-content-between recent-activities-times mb-1">
                                                                <p class="fs-14"><span class="user-name">Emily R.</span>
                                                                    <strong>has signed</strong>
                                                                </p>
                                                                <span class="red fs-14">1 week ago</span>
                                                            </div>
                                                            <p class="fs-14">Ban Single-Use Plastics</p>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div
                                                                class="d-flex justify-content-between recent-activities-times mb-1">
                                                                <p class="fs-14"><span class="user-name">Michael
                                                                        B.</span>
                                                                    <strong>has signed</strong>
                                                                </p>
                                                                <span class="red fs-14">2 weeks ago</span>
                                                            </div>
                                                            <p class="fs-14">Improve Access to Clean Drinking Water</p>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div
                                                                class="d-flex justify-content-between recent-activities-times mb-1">
                                                                <p class="fs-14"><span class="user-name">Sophia L.</span>
                                                                    <strong>has signed</strong>
                                                                </p>
                                                                <span class="red fs-14">3 weeks ago</span>
                                                            </div>
                                                            <p class="fs-14">Promote Equal Education Opportunities</p>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div
                                                                class="d-flex justify-content-between recent-activities-times mb-1">
                                                                <p class="fs-14"><span class="user-name">Liam W.</span>
                                                                    <strong>has signed</strong>
                                                                </p>
                                                                <span class="red fs-14">1 month ago</span>
                                                            </div>
                                                            <p class="fs-14">Save Endangered Rainforests</p>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div
                                                                class="d-flex justify-content-between recent-activities-times mb-1">
                                                                <p class="fs-14"><span class="user-name">Olivia K.</span>
                                                                    <strong>has signed</strong>
                                                                </p>
                                                                <span class="red fs-14">1 month ago</span>
                                                            </div>
                                                            <p class="fs-14">Advocate for Mental Health Awareness</p>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div
                                                                class="d-flex justify-content-between recent-activities-times mb-1">
                                                                <p class="fs-14"><span class="user-name">Noah J.</span>
                                                                    <strong>has signed</strong>
                                                                </p>
                                                                <span class="red fs-14">2 months ago</span>
                                                            </div>
                                                            <p class="fs-14">Reduce Carbon Emissions Globally</p>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <div
                                                                class="d-flex justify-content-between recent-activities-times mb-1">
                                                                <p class="fs-14"><span class="user-name">Emma P.</span>
                                                                    <strong>has signed</strong>
                                                                </p>
                                                                <span class="red fs-14">3 months ago</span>
                                                            </div>
                                                            <p class="fs-14">Ensure Fair Wages for All Workers</p>
                                                        </a>
                                                    </li>
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
                @foreach ($categories as $name)
                    <div class="col-lg-3 col-sm-6 mb-3">
                        <a href="#" class="category-card d-block">
                            <span class="category-icon"><i class="bi bi-house-check"></i></span>
                            <h3 class="h5">{{ $name }}</h3>
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
