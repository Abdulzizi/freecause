@extends('layouts.legacy')

@php
$isAuthed = auth()->check();
@endphp

@section('title', ($petition->title ?? 'Petition') . ' - FreeCause')

@php
$petitionTitle = $petition->title ?? 'Petition';
$petitionImg = $petition->coverUrl(); // dari model helper kamu
$petitionCredit = $petition->image_credit ?? ''; // kalau belum ada kolom, biarin kosong
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
                            @if($petitionCredit)
                                <span class="fc-img-credit">{{ $petitionCredit }}</span>
                            @endif
                        </a>

                        <h2 class="mt-3 mb-2" style="font-size:16px;font-weight:700;">
                            {{ $petitionTitle }}
                        </h2>

                        <div class="fc-content">
                            <h3>Introduction</h3>
                            <p>{!! nl2br(e($petition->description ?? '')) !!}</p>
                        </div>

                        <div class="fc-dots my-4"></div>

                        <div id="signFormBottom" class="bg-light rounded-3 p-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">Sign The Petition</div>

                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            @if(!$isAuthed)
                                <div class="d-flex gap-2 justify-content-center flex-wrap my-3">
                                    <a class="btn fc-google-btn fc-oauth-btn"
                                        href="{{ lroute('oauth.google', ['flow' => 'petition', 'petition_id' => $petition->id, 'slug' => $petition->slug]) }}">
                                        <img src="{{ asset('legacy/images-v2/google.png') }}" alt="Google">
                                        Continue with Google
                                    </a>
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
                            @endif

                            @include('petition.partials._sign_form', ['variant' => 'split', 'petition' => $petition])
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

                            @if(!$isAuthed)
                                <div class="d-flex gap-2 justify-content-center flex-wrap my-3">
                                    <a class="btn fc-google-btn fc-oauth-btn"
                                        href="{{ lroute('oauth.google', ['flow' => 'petition', 'petition_id' => $petition->id, 'slug' => $petition->slug]) }}">
                                        <img src="{{ asset('legacy/images-v2/google.png') }}" alt="Google">
                                        Continue with Google
                                    </a>
                                </div>

                                <div class="fc-or my-3"><span>OR</span></div>

                                <p class="mb-3">
                                    If you already have an account <a class="red" href="/{{ $locale }}/login"><em>please sign in</em></a>
                                </p>
                            @endif

                            @include('petition.partials._sign_form', ['variant' => 'stack', 'petition' => $petition])
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
                                @forelse($latest as $i => $sig)
                                    <div class="fc-latest-row">
                                        <div class="fc-latest-date">
                                            {{ optional($sig->created_at)->format('j F Y') }}
                                        </div>
                                        <div>
                                            <strong>{{ $goalCurrent - $i }}.</strong>
                                            <a href="#" class="red">{{ $sig->name ?? 'Anonymous' }}</a>
                                            <span class="text-muted">|</span>
                                            <span>{{ $sig->comment ?? 'I support this petition' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted" style="font-size:14px;">no signatures yet</div>
                                @endforelse

                                <a href="#" class="btn btn-sm btn-danger mt-2">browse all the signatures »</a>
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
                                    <a href="#" class="red">{{ $petition->user?->name ?? 'Demo User' }}</a>
                                </div>

                                <div class="d-flex justify-content-between gap-2">
                                    <strong>In:</strong>
                                    <a href="{{ route('petitions.byCategory', ['locale' => $locale, 'categorySlug' => $petition->category?->slug ?? '-', 'category' => $petition->category?->id ?? 0]) }}" class="red">{{ $petition->category?->name ?? '-' }}</a>
                                </div>

                                <div class="mt-2">
                                    <strong>Petition target:</strong><br>
                                    {{ $petition->target ?? '-' }}
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
                                        readonly><script src="{{ url("/widgets/pbadge/{$petition->id}") }}"></script></textarea>

                                    <div class="mb-2"><strong>468×60</strong></div>
                                    <textarea class="form-control mb-3" rows="3"
                                        readonly><script src="{{ url("/widgets/pbadge/{$petition->id}") }}"></script></textarea>

                                    <div class="mb-2"><strong>336×280</strong></div>
                                    <textarea class="form-control mb-3" rows="3"
                                        readonly><script src="{{ url("/widgets/pbadge/{$petition->id}") }}"></script></textarea>

                                    <div class="mb-2"><strong>125×125</strong></div>
                                    <textarea class="form-control" rows="3"
                                        readonly><script src="{{ url("/widgets/pbadge/{$petition->id}") }}"></script></textarea>
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
