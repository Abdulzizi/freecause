@extends('layouts.legacy')

@php
    $isAuthed = auth()->check();

    $petitionTitle = e($tr->title ?? ($petition->title ?? 'Petition'));
    $pageTitle = __('messages.show.page_title', ['title' => $petitionTitle]);

    $petitionImg = $petition->coverUrl();
    $petitionCredit = $petition->image_credit ?? '';

    $btnSignNow = __('messages.show.btn_sign_now');
    $boxSignTitle = __('messages.show.box_sign_title');
    $googleContinue = __('messages.show.google_continue');
    $orText = __('messages.show.or');

    $loginUrl = lroute('login');

    $authHintSplit = __('messages.show.auth_hint_split', ['login_url' => $loginUrl]);
    $authHintStack = __('messages.show.auth_hint_stack', ['login_url' => $loginUrl]);

    $boxShoutbox = __('messages.show.box_shoutbox');

    $boxGoal = __('messages.show.box_goal');
    $goalSignaturesText = __('messages.show.goal_signatures', ['count' => number_format($goalCurrent)]);
    $goalLabelText = __('messages.show.goal_label', ['count' => number_format($goalTotal)]);

    $boxLatest = __('messages.show.box_latest');
    $latestEmpty = __('messages.show.latest_empty');
    $latestBrowseAll = __('messages.show.latest_browse_all');

    $boxInformation = __('messages.show.box_information');
    $infoBy = __('messages.show.info_by');
    $infoIn = __('messages.show.info_in');
    $infoTarget = __('messages.show.info_target');

    $boxTags = __('messages.show.box_tags');
    $tagsEmpty = __('messages.show.tags_empty');

    $boxEmbed = __('messages.show.box_embed');
    $embedDirect = __('messages.show.embed_direct');
    $embedHtml = __('messages.show.embed_html');
    $embedForumNoTitle = __('messages.show.embed_forum_no_title');
    $embedForumWithTitle = __('messages.show.embed_forum_with_title');

    $boxWidgets = __('messages.show.box_widgets');
@endphp

@section('title', $pageTitle)
@section('og_title', $petitionTitle)
@section('og_description', \Illuminate\Support\Str::limit(strip_tags($tr->description ?? ''), 200))
@section('og_image', $petitionImg)
@section('og_url', request()->url())

@section('content')
    <section class="py-5">
        <div class="container">

            <div class="bg-white shadow-sm rounded-3 p-4" style="border:1px solid #eee;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h1 class="mb-0" style="font-size:22px;font-weight:700;line-height:1.3;">
                        {{ $petitionTitle }}
                    </h1>

                    @if (!$hasSigned)
                        <a href="#signFormTop" class="btn btn-danger fc-sign-now">{{ $btnSignNow }}</a>
                    @endif
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <a href="#" class="fc-img-wrap" data-bs-toggle="modal" data-bs-target="#imgModal">
                            <img src="{{ $petitionImg }}" alt="" class="fc-petition-img-blur" aria-hidden="true">
                            <img src="{{ $petitionImg }}" alt="petition image" class="fc-petition-img" loading="lazy">
                            @if ($petitionCredit)
                                <span class="fc-img-credit">{{ $petitionCredit }}</span>
                            @endif
                        </a>

                        <div class="fc-content mt-3">
                            <div class="fc-petition-description">
                                {!! $tr->description !!}
                            </div>
                        </div>

                        <div class="fc-dots my-4"></div>

                        <div id="signFormBottom" class="bg-light rounded-3 p-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">{{ $boxSignTitle }}</div>

                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            @if (!$isAuthed)
                                <div class="d-flex gap-2 justify-content-center flex-wrap my-3">
                                    <a class="btn fc-google-btn fc-oauth-btn"
                                        href="{{ lroute('oauth.google', ['flow' => 'petition', 'petition_id' => $petition->id, 'slug' => $tr->slug]) }}">
                                        <img src="{{ asset('legacy/images-v2/google.png') }}" alt="Google">
                                        {{ $googleContinue }}
                                    </a>
                                </div>

                                <div class="fc-or my-3"><span>{{ $orText }}</span></div>

                                <p class="mb-3">{!! $authHintSplit !!}</p>
                            @endif

                            @include('petition.partials._sign_form', [
                                'variant' => 'split',
                                'petition' => $petition,
                                'tr' => $tr,
                                'locale' => $locale,
                                'hasSigned' => $hasSigned,
                                'content' => $formContent,
                            ])
                        </div>

                        <div class="mt-4">
                            <div class="fc-box-title">{{ $boxShoutbox }}</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div id="signFormTop" class="bg-light rounded-3 p-4 mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">{{ $boxSignTitle }}</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            @if (!$isAuthed)
                                <div class="d-flex gap-2 justify-content-center flex-wrap my-3">
                                    <a class="btn fc-google-btn fc-oauth-btn"
                                        href="{{ lroute('oauth.google', ['flow' => 'petition', 'petition_id' => $petition->id, 'slug' => $tr->slug]) }}">
                                        <img src="{{ asset('legacy/images-v2/google.png') }}" alt="Google">
                                        {{ $googleContinue }}
                                    </a>
                                </div>

                                <div class="fc-or my-3"><span>{{ $orText }}</span></div>

                                <p class="mb-3">{!! $authHintStack !!}</p>
                            @endif

                            @include('petition.partials._sign_form', [
                                'variant' => 'stack',
                                'petition' => $petition,
                                'tr' => $tr,
                                'locale' => $locale,
                                'hasSigned' => $hasSigned,
                                'content' => $formContent,
                            ])
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">{{ $boxGoal }}</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            <div class="fc-progress mt-3">
                                <div class="fc-progress-bar" style="width: {{ $pct }}%;"></div>
                            </div>

                            <div class="d-flex justify-content-between mt-2" style="font-size:14px;">
                                <div>{{ $goalSignaturesText }}</div>
                                <div class="text-muted">{{ $goalLabelText }}</div>
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title d-flex align-items-center justify-content-between">
                                <span>{{ $boxLatest }}</span>
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
                                            <a href="{{ lroute('petition.signatures', ['slug' => $tr->slug, 'id' => $petition->id]) }}"
                                                class="red">{{ $sig->name ?? 'Anonymous' }}</a>
                                            <span class="text-muted">|</span>
                                            <span>{{ $sig->text ?? 'I support this petition' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted" style="font-size:14px;">{{ $latestEmpty }}</div>
                                @endforelse

                                <a href="{{ lroute('petition.signatures', ['slug' => $tr->slug, 'id' => $petition->id]) }}"
                                    class="btn btn-sm btn-danger mt-2">{{ $latestBrowseAll }}</a>
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">{{ $boxInformation }}</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            <div class="mt-3" style="font-size:14px;line-height:1.8;">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong>{{ $infoBy }}</strong>
                                    @if ($petition->user)
                                        <a href="{{ lroute('user.profile', ['slug' => \Illuminate\Support\Str::slug($petition->user->name ?? 'user') ?: 'user', 'id' => $petition->user_id]) }}"
                                            class="red">
                                            {{ $petition->user->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between gap-2">
                                    <strong>{{ $infoIn }}</strong>
                                    <a href="{{ lroute('petitions.byCategory', ['categorySlug' => $petition->category?->slug ?? '-', 'category' => $petition->category?->id ?? 0]) }}"
                                        class="red">
                                        {{ $petition->category?->name ?? '-' }}
                                    </a>
                                </div>

                                <div class="mt-2">
                                    <strong>{{ $infoTarget }}</strong><br>
                                    {{ $petition->target ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">{{ $boxTags }}</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>
                            <div class="mt-3" style="font-size:14px;">
                                @if ($petition->tags)
                                    @foreach (explode(',', $petition->tags) as $tag)
                                        @if (trim($tag))
                                            <span class="badge bg-secondary me-1 mb-1"
                                                style="font-weight:400;font-size:13px;">{{ trim($tag) }}</span>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="text-muted">{{ $tagsEmpty }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">{{ $boxEmbed }}</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            <div class="mt-3" style="font-size:13px;">
                                <div class="mb-2"><strong>{{ $embedDirect }}</strong></div>
                                <input class="form-control mb-3" value="{{ $directLink }}" readonly>

                                <div class="mb-2"><strong>{{ $embedHtml }}</strong></div>
                                <input class="form-control mb-3"
                                    value='<a href="{{ $directLink }}">{{ $petitionTitle }}</a>' readonly>

                                <div class="mb-2"><strong>{{ $embedForumNoTitle }}</strong></div>
                                <input class="form-control mb-3" value='[URL="{{ $directLink }}"][/URL]' readonly>

                                <div class="mb-2"><strong>{{ $embedForumWithTitle }}</strong></div>
                                <input class="form-control" value='[URL]{{ $directLink }}[/URL]' readonly>
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm fc-widgets-box" style="border:1px solid #eee;">
                            <button id="widgetsToggle" class="fc-widgets-toggle" type="button"
                                data-bs-toggle="collapse" data-bs-target="#widgetsBody" aria-expanded="false"
                                aria-controls="widgetsBody">
                                <span class="fc-box-title mb-0 d-flex align-items-center gap-2">
                                    {{ $boxWidgets }}
                                    <i class="fa fa-angle-down fc-widgets-arrow cursor-pointer" aria-hidden="true"></i>
                                </span>
                            </button>

                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div
                                    style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                </div>
                            </div>

                            <div id="widgetsBody" class="collapse">
                                <div class="mt-3">
                                    <div class="mb-2"><strong>728×90</strong></div>
                                    <textarea class="form-control mb-3" rows="3" readonly><script src="{{ url("/widgets/pbadge/{$petition->id}") }}"></script></textarea>

                                    <div class="mb-2"><strong>468×60</strong></div>
                                    <textarea class="form-control mb-3" rows="3" readonly><script src="{{ url("/widgets/pbadge/{$petition->id}") }}"></script></textarea>

                                    <div class="mb-2"><strong>336×280</strong></div>
                                    <textarea class="form-control mb-3" rows="3" readonly><script src="{{ url("/widgets/pbadge/{$petition->id}") }}"></script></textarea>

                                    <div class="mb-2"><strong>125×125</strong></div>
                                    <textarea class="form-control" rows="3" readonly><script src="{{ url("/widgets/pbadge/{$petition->id}") }}"></script></textarea>
                                </div>
                            </div>
                        </div>

                        @auth
                            @if ((int) auth()->id() === (int) $petition->user_id)
                                <div class="bg-white rounded-3 p-4 shadow-sm my-4" style="border:1px solid #eee;">
                                    <div class="fc-box-title">Operations</div>
                                    <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                        <div
                                            style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                                        </div>
                                    </div>

                                    <div style="font-size:14px;line-height:1.9;">
                                        <div>
                                            <a class="red"
                                                href="{{ lroute('petition.edit', ['slug' => $tr->slug, 'id' => $petition->id]) }}">Edit</a>
                                        </div>

                                        <div class="mt-2">
                                            Download signatures :
                                            <a class="red"
                                                href="{{ lroute('petition.download.txt', ['slug' => $tr->slug, 'id' => $petition->id]) }}">TXT</a>
                                            <span class="text-muted"> </span>
                                            <a class="red"
                                                href="{{ lroute('petition.download.pdf', ['slug' => $tr->slug, 'id' => $petition->id]) }}">PDF</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endauth

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
                    <img src="{{ $petitionImg }}" class="img-fluid w-100" loading="lazy" alt="petition image large">
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .fc-petition-description ul {
        list-style: disc !important;
        padding-left: 22px !important;
        margin: 0 0 14px 0 !important;
    }

    .fc-petition-description ol {
        list-style: decimal !important;
        padding-left: 22px !important;
        margin: 0 0 14px 0 !important;
    }

    .fc-petition-description p {
        margin: 0 0 12px;
    }

    .fc-petition-description {
        font-size: 16px;
        line-height: 1.75;
        color: #222;
    }

    .fc-petition-description li {
        margin: 0 0 6px 0;
    }

    .fc-petition-description li>ul,
    .fc-petition-description li>ol {
        margin-top: 8px !important;
    }

    .fc-img-wrap {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        background: #1a1a1a;
        height: 380px;
    }

    .fc-petition-img-blur {
        position: absolute;
        inset: -15px;
        width: calc(100% + 30px);
        height: calc(100% + 30px);
        object-fit: cover;
        filter: blur(18px);
        opacity: 0.55;
        border-radius: 0;
    }

    .fc-petition-img {
        position: relative;
        z-index: 1;
        width: 100%;
        height: 380px;
        object-fit: contain;
        display: block;
    }

    .fc-img-credit {
        position: absolute;
        right: 10px;
        bottom: 10px;
        background: rgba(0, 0, 0, .55);
        color: #fff;
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 6px;
    }
</style>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('widgetsToggle');
            const body = document.getElementById('widgetsBody');
            if (!toggle || !body || !window.bootstrap) return;

            body.addEventListener('shown.bs.collapse', () => {
                toggle.removeAttribute('data-bs-toggle');
                toggle.removeAttribute('data-bs-target');
                toggle.removeAttribute('aria-controls');
                toggle.style.cursor = 'default';
            }, {
                once: true
            });
        });
    </script>
@endpush
