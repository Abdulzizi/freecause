@extends('layouts.legacy')

@section('title', 'Protect Our Oceans: Ban Single-Use Plastics Worldwide - FreeCause')

@php
    // phase 1: static mock
    $petitionTitle = 'Protect Our Oceans: Ban Single-Use Plastics Worldwide';
    $petitionImg = asset('legacy/images/demo-featured.jpg'); // put any image here
    $petitionCredit = '© AP Images/European Union-EP';

    $goalTotal = 100000;
    $goalCurrent = 1000;
    $pct = min(100, round(($goalCurrent / $goalTotal) * 100));

    $latest = [
        ['num' => 1000, 'name' => 'Jean B', 'date' => '2 March 2016', 'comment' => 'I support this petition'],
        ['num' => 999, 'name' => 'Adrian D', 'date' => '1 March 2016', 'comment' => 'I support this petition'],
        ['num' => 998, 'name' => 'Sheryl Leed', 'date' => '28 February 2016', 'comment' => 'I support this petition'],
        ['num' => 997, 'name' => 'Mette Henderson', 'date' => '28 February 2016', 'comment' => 'I support this petition'],
        ['num' => 996, 'name' => 'Marianna A', 'date' => '27 February 2016', 'comment' => 'I support this petition'],
        ['num' => 995, 'name' => 'Juliana D', 'date' => '27 February 2016', 'comment' => 'I support this petition'],
        ['num' => 994, 'name' => 'Lisa Z', 'date' => '27 February 2016', 'comment' => 'I support this petition'],
        ['num' => 993, 'name' => 'Brian T', 'date' => '27 February 2016', 'comment' => 'I support this petition'],
        ['num' => 992, 'name' => 'Kim W', 'date' => '26 February 2016', 'comment' => 'I support this petition'],
        ['num' => 991, 'name' => 'A S', 'date' => '25 February 2016', 'comment' => 'I support this petition'],
        ['num' => 990, 'name' => 'Kate D', 'date' => '25 February 2016', 'comment' => 'I support this petition'],
        ['num' => 989, 'name' => 'Misty W', 'date' => '25 February 2016', 'comment' => 'I support this petition'],
        ['num' => 988, 'name' => 'Candy H', 'date' => '24 February 2016', 'comment' => 'I support this petition'],
        ['num' => 987, 'name' => 'Justin Wagner', 'date' => '24 February 2016', 'comment' => 'I support this petition'],
        ['num' => 986, 'name' => 'Kelly M', 'date' => '24 February 2016', 'comment' => 'I support this petition'],
        ['num' => 985, 'name' => 'Susan G', 'date' => '24 February 2016', 'comment' => 'I support this petition'],
        ['num' => 984, 'name' => 'Joyce J', 'date' => '23 February 2016', 'comment' => 'I support this petition'],
        ['num' => 983, 'name' => 'Kim F', 'date' => '23 February 2016', 'comment' => 'I support this petition'],
        ['num' => 982, 'name' => 'Anthony J', 'date' => '22 February 2016', 'comment' => 'I support this petition'],
        ['num' => 981, 'name' => 'Larry S', 'date' => '22 February 2016', 'comment' => 'I support this petition'],
        ['num' => 980, 'name' => 'Gabriela V', 'date' => '22 February 2016', 'comment' => 'I support this petition'],
        ['num' => 979, 'name' => 'Roger M', 'date' => '22 February 2016', 'comment' => 'I support this petition'],
        ['num' => 978, 'name' => 'David S', 'date' => '21 February 2016', 'comment' => 'I support this petition'],
        ['num' => 977, 'name' => 'Sarah M', 'date' => '21 February 2016', 'comment' => 'I support this petition'],
        ['num' => 976, 'name' => 'Tammy W', 'date' => '21 February 2016', 'comment' => 'I support this petition'],
        ['num' => 975, 'name' => 'Sue S', 'date' => '21 February 2016', 'comment' => 'I support this petition'],
        ['num' => 974, 'name' => '...', 'date' => '', 'comment' => ''],
    ];

    $directLink = url("/{$locale}/petition/{$slug}/{$id}");
@endphp

@section('content')
    <section class="py-5">
        <div class="container">

            {{-- TOP BOX --}}
            <div class="bg-white shadow-sm rounded-3 p-4" style="border:1px solid #eee;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h1 class="mb-0" style="font-size:22px;font-weight:700;line-height:1.3;">
                        {{ $petitionTitle }}
                    </h1>

                    <a href="#signFormTop" class="btn btn-danger fc-sign-now">Sign Now</a>
                </div>

                <div class="row g-4">
                    {{-- LEFT --}}
                    <div class="col-lg-8">
                        <a href="#" class="fc-img-wrap" data-bs-toggle="modal" data-bs-target="#imgModal">
                            <img src="{{ $petitionImg }}" alt="petition image" class="img-fluid fc-petition-img">
                            <span class="fc-img-credit">{{ $petitionCredit }}</span>
                        </a>

                        <h2 class="mt-3 mb-2" style="font-size:16px;font-weight:700;">
                            {{ $petitionTitle }}
                        </h2>

                        <div class="fc-content">
                            <h3>Introduction</h3>
                            <p>
                                The oceans, which cover more than 70% of our planet, are under siege from plastic pollution.
                                Every year, an estimated <strong>8 million tons of plastic waste</strong> enter the
                                oceans...
                            </p>

                            <h3>The Devastating Impact of Single-Use Plastics</h3>

                            <h4>1. Marine Life Under Threat</h4>
                            <p>Plastic pollution has catastrophic effects on marine life...</p>

                            <h4>2. Human Health Risks</h4>
                            <p>Microplastics have been found in fish, shellfish, and even human blood...</p>

                            <h4>3. The Economic Cost of Plastic Pollution</h4>
                            <p>Plastic waste damages industries such as fisheries, tourism, and coastal communities...</p>

                            <h3>Our Demands</h3>
                            <p>This petition calls on world leaders, policymakers, and businesses to:</p>
                            <ul>
                                <li>Enact legislation to ban single-use plastics</li>
                                <li>Promote sustainable alternatives</li>
                                <li>Improve waste management and cleanup efforts</li>
                            </ul>

                            <p class="mb-0">
                                By signing this petition, you are sending a strong message that we demand real change.
                            </p>
                        </div>

                        {{-- dotted separator + bottom sign form (like prod) --}}
                        <div class="fc-dots my-4"></div>

                        <div id="signFormBottom" class="bg-light rounded-3 p-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">Sign The Petition</div>

                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-center flex-wrap my-3">
                                <button type="button" class="btn fc-facebook-btn fc-oauth-btn">
                                    <img src="{{ asset('legacy/images-v2/facebook.png') }}" alt="Facebook">
                                    Continue with Facebook
                                </button>

                                <button type="button" class="btn fc-google-btn fc-oauth-btn">
                                    <img src="{{ asset('legacy/images-v2/google.png') }}" alt="Google">
                                    Continue with Google
                                </button>
                            </div>

                            <div class="fc-or my-3"><span>OR</span></div>

                            <p class="mb-3">
                                If you already have an account <a class="red" href="/{{ $locale }}/login">please sign
                                    in</a>,
                                otherwise <strong>register an account</strong> for free then sign the petition filling the
                                fields below.
                                <br>
                                Email and password will be your account data, you will be able to sign other petitions after
                                logging in.
                            </p>

                            @include('petition.partials._sign_form', ['variant' => 'split'])
                        </div>

                        <div class="mt-4">
                            <div class="fc-box-title">Shoutbox</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>
                            {{-- <div class="text-muted" style="font-size:14px;">(stub) shoutbox/comments later</div> --}}
                        </div>
                    </div>

                    {{-- RIGHT SIDEBAR --}}
                    <div class="col-lg-4">
                        <div id="signFormTop" class="bg-light rounded-3 p-4 mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">Sign The Petition</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-center flex-wrap my-3">
                                <button type="button" class="btn fc-facebook-btn fc-oauth-btn">
                                    <img src="{{ asset('legacy/images-v2/facebook.png') }}" alt="Facebook">
                                    Continue with Facebook
                                </button>

                                <button type="button" class="btn fc-google-btn fc-oauth-btn">
                                    <img src="{{ asset('legacy/images-v2/google.png') }}" alt="Google">
                                    Continue with Google
                                </button>
                            </div>

                            <div class="fc-or my-3"><span>OR</span></div>

                            <p class="mb-3">
                                If you already have an account <a class="red" href="/{{ $locale }}/login"><em>please sign
                                        in</em></a>
                            </p>

                            @include('petition.partials._sign_form', ['variant' => 'stack'])
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">Goal</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            <div class="fc-progress mt-3">
                                <div class="fc-progress-bar" style="width: {{ $pct }}%;"></div>
                            </div>

                            <div class="d-flex justify-content-between mt-2" style="font-size:14px;">
                                <div>{{ number_format($goalCurrent) }} signatures</div>
                                <div class="text-muted">Goal: {{ number_format($goalTotal) }}</div>
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title d-flex align-items-center justify-content-between">
                                <span>Latest Signatures</span>
                            </div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            <div class="fc-latest mt-3">
                                @foreach($latest as $row)
                                    @if($row['num'] === 974)
                                        <a href="#" class="btn btn-sm btn-danger mt-2">browse all the signatures »</a>
                                        @break
                                    @endif

                                    <div class="fc-latest-row">
                                        <div class="fc-latest-date">{{ $row['date'] }}</div>
                                        <div>
                                            <strong>{{ $row['num'] }}.</strong>
                                            <a href="#" class="red">{{ $row['name'] }}</a>
                                            <span class="text-muted">|</span>
                                            <span>{{ $row['comment'] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">Information</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            <div class="mt-3" style="font-size:14px;line-height:1.8;">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong>By:</strong>
                                    <a href="#" class="red">Bettie Kirby</a>
                                </div>
                                <div class="d-flex justify-content-between gap-2">
                                    <strong>In:</strong>
                                    <a href="#" class="red">School and Education</a>
                                </div>
                                <div class="mt-2">
                                    <strong>Petition target:</strong><br>
                                    President, Congress, BP Oil, U.S. Coast Guard, E.P.A.
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">Tags</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>
                            <div class="mt-3 text-muted" style="font-size:14px;">No tags</div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">Embed Codes</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            <div class="mt-3" style="font-size:13px;">
                                <div class="mb-2"><strong>direct link</strong></div>
                                <input class="form-control mb-3" value="{{ $directLink }}" readonly>

                                <div class="mb-2"><strong>link for html</strong></div>
                                <input class="form-control mb-3"
                                    value='<a href="{{ $directLink }}">{{ $petitionTitle }}</a>' readonly>

                                <div class="mb-2"><strong>link for forum without title</strong></div>
                                <input class="form-control mb-3" value='[URL="{{ $directLink }}"][/URL]' readonly>

                                <div class="mb-2"><strong>link for forum with title</strong></div>
                                <input class="form-control" value='[URL]{{ $directLink }}[/URL]' readonly>
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm fc-widgets-box" style="border:1px solid #eee;">
                            <button id="widgetsToggle" class="fc-widgets-toggle" type="button" data-bs-toggle="collapse"
                                data-bs-target="#widgetsBody" aria-expanded="false" aria-controls="widgetsBody">
                                <span class="fc-box-title mb-0 d-flex align-items-center gap-2">
                                    Widgets
                                    <i class="fa fa-angle-down fc-widgets-arrow cursor-pointer" aria-hidden="true"></i>
                                </span>
                            </button>

                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            {{-- closed first load --}}
                            <div id="widgetsBody" class="collapse">
                                <div class="mt-3">
                                    <div class="mb-2"><strong>728×90</strong></div>
                                    <textarea class="form-control mb-3" rows="3"
                                        readonly><script src="{{ url("/widgets/pbadge/{$id}") }}"></script></textarea>

                                    <div class="mb-2"><strong>468×60</strong></div>
                                    <textarea class="form-control mb-3" rows="3"
                                        readonly><script src="{{ url("/widgets/pbadge/{$id}") }}"></script></textarea>

                                    <div class="mb-2"><strong>336×280</strong></div>
                                    <textarea class="form-control mb-3" rows="3"
                                        readonly><script src="{{ url("/widgets/pbadge/{$id}") }}"></script></textarea>

                                    <div class="mb-2"><strong>125×125</strong></div>
                                    <textarea class="form-control" rows="3"
                                        readonly><script src="{{ url("/widgets/pbadge/{$id}") }}"></script></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>

    <div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content p-2">
                <div class="modal-body p-0 position-relative">
                    <button type="button" class="btn-close position-absolute" style="right:10px;top:10px;z-index:2;"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                    <img src="{{ $petitionImg }}" class="img-fluid w-100" alt="petition image large">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('widgetsToggle');
    const body = document.getElementById('widgetsBody');
    if (!toggle || !body || !window.bootstrap) return;

    body.addEventListener('shown.bs.collapse', () => {
        // hide the arrow cleanly
        // const arrow = toggle.querySelector('.fc-widgets-arrow');
        // if (arrow) arrow.remove();

        // disable further collapsing (no second click)
        toggle.removeAttribute('data-bs-toggle');
        toggle.removeAttribute('data-bs-target');
        toggle.removeAttribute('aria-controls');
        toggle.style.cursor = 'default';
        // toggle.setAttribute('disabled', 'disabled');
    }, { once: true });
});
</script>
@endpush
