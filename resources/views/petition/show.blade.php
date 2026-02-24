@extends('layouts.legacy')

@php
    $isAuthed = auth()->check();

    $content = $content ?? collect();

    $petitionTitle = $tr->title ?? ($petition->title ?? 'Petition');
    $pageTitleTmpl = $content['title'] ?? ':title - FreeCause';
    $pageTitle = str_replace(':title', $petitionTitle, $pageTitleTmpl);

    $petitionImg = $petition->coverUrl();
    $petitionCredit = $petition->image_credit ?? '';

    $btnSignNow     = $content['btn_sign_now'] ?? 'Sign Now';
    $boxSignTitle   = $content['box_sign_title'] ?? 'Sign The Petition';
    $googleContinue = $content['google_continue'] ?? 'Continue with Google';
    $orText         = $content['or'] ?? 'OR';

    $loginUrl = lroute('login');

    $authHintSplit = $content['auth_hint_split']
        ?? 'If you already have an account <a class="red" href=":login_url">please sign in</a>, otherwise <strong>register an account</strong> for free then sign the petition filling the fields below.<br>Email and password will be your account data, you will be able to sign other petitions after logging in.';
    $authHintStack = $content['auth_hint_stack']
        ?? 'If you already have an account <a class="red" href=":login_url"><em>please sign in</em></a>';

    $authHintSplit = str_replace(':login_url', $loginUrl, $authHintSplit);
    $authHintStack = str_replace(':login_url', $loginUrl, $authHintStack);

    $boxShoutbox = $content['box_shoutbox'] ?? 'Shoutbox';

    $boxGoal = $content['box_goal'] ?? 'Goal';
    $goalSignaturesTmpl = $content['goal_signatures'] ?? ':count signatures';
    $goalLabelTmpl      = $content['goal_label'] ?? 'Goal: :count';
    $goalSignaturesText = str_replace(':count', number_format($goalCurrent), $goalSignaturesTmpl);
    $goalLabelText      = str_replace(':count', number_format($goalTotal), $goalLabelTmpl);

    $boxLatest = $content['box_latest'] ?? 'Latest Signatures';
    $latestEmpty = $content['latest_empty'] ?? 'no signatures yet';
    $latestBrowseAll = $content['latest_browse_all'] ?? 'browse all the signatures »';

    $boxInformation = $content['box_information'] ?? 'Information';
    $infoBy = $content['info_by'] ?? 'By:';
    $infoIn = $content['info_in'] ?? 'In:';
    $infoTarget = $content['info_target'] ?? 'Petition target:';

    $boxTags = $content['box_tags'] ?? 'Tags';
    $tagsEmpty = $content['tags_empty'] ?? 'No tags';

    $boxEmbed = $content['box_embed'] ?? 'Embed Codes';
    $embedDirect = $content['embed_direct'] ?? 'direct link';
    $embedHtml = $content['embed_html'] ?? 'link for html';
    $embedForumNoTitle = $content['embed_forum_no_title'] ?? 'link for forum without title';
    $embedForumWithTitle = $content['embed_forum_with_title'] ?? 'link for forum with title';

    $boxWidgets = $content['box_widgets'] ?? 'Widgets';
@endphp

@section('title', $pageTitle)

@section('content')
    <section class="py-5">
        <div class="container">

            <div class="bg-white shadow-sm rounded-3 p-4" style="border:1px solid #eee;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h1 class="mb-0" style="font-size:22px;font-weight:700;line-height:1.3;">
                        {{ $petitionTitle }}
                    </h1>

                    <a href="#signFormTop" class="btn btn-danger fc-sign-now">{{ $btnSignNow }}</a>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <a href="#" class="fc-img-wrap" data-bs-toggle="modal" data-bs-target="#imgModal">
                            <img src="{{ $petitionImg }}" alt="petition image" class="img-fluid fc-petition-img">
                            @if($petitionCredit)
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
                                <div style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;"></div>
                            </div>

                            @if(!$isAuthed)
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
                                <div style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div id="signFormTop" class="bg-light rounded-3 p-4 mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">{{ $boxSignTitle }}</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;"></div>
                            </div>

                            @if(!$isAuthed)
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
                                <div style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;"></div>
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
                                <div style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;"></div>
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
                                            <span>{{ $sig->comment ?? ($formContent['default_comment'] ?? 'I support this petition') }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted" style="font-size:14px;">{{ $latestEmpty }}</div>
                                @endforelse

                                <a href="#" class="btn btn-sm btn-danger mt-2">{{ $latestBrowseAll }}</a>
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">{{ $boxInformation }}</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;"></div>
                            </div>

                            <div class="mt-3" style="font-size:14px;line-height:1.8;">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong>{{ $infoBy }}</strong>
                                    <a href="#" class="red">{{ $petition->user?->name ?? 'Demo User' }}</a>
                                </div>

                                <div class="d-flex justify-content-between gap-2">
                                    <strong>{{ $infoIn }}</strong>
                                    <a href="{{ lroute('petitions.byCategory', ['categorySlug' => $petition->category?->slug ?? '-', 'category' => $petition->category?->id ?? 0]) }}" class="red">
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
                                <div style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;"></div>
                            </div>
                            <div class="mt-3 text-muted" style="font-size:14px;">
                                {{ $tagsEmpty }}
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4" style="border:1px solid #eee;">
                            <div class="fc-box-title">{{ $boxEmbed }}</div>
                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;"></div>
                            </div>

                            <div class="mt-3" style="font-size:13px;">
                                <div class="mb-2"><strong>{{ $embedDirect }}</strong></div>
                                <input class="form-control mb-3" value="{{ $directLink }}" readonly>

                                <div class="mb-2"><strong>{{ $embedHtml }}</strong></div>
                                <input class="form-control mb-3" value='<a href="{{ $directLink }}">{{ $petitionTitle }}</a>' readonly>

                                <div class="mb-2"><strong>{{ $embedForumNoTitle }}</strong></div>
                                <input class="form-control mb-3" value='[URL="{{ $directLink }}"][/URL]' readonly>

                                <div class="mb-2"><strong>{{ $embedForumWithTitle }}</strong></div>
                                <input class="form-control" value='[URL]{{ $directLink }}[/URL]' readonly>
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm fc-widgets-box" style="border:1px solid #eee;">
                            <button id="widgetsToggle" class="fc-widgets-toggle" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#widgetsBody" aria-expanded="false" aria-controls="widgetsBody">
                                <span class="fc-box-title mb-0 d-flex align-items-center gap-2">
                                    {{ $boxWidgets }}
                                    <i class="fa fa-angle-down fc-widgets-arrow cursor-pointer" aria-hidden="true"></i>
                                </span>
                            </button>

                            <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                <div style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;"></div>
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
                            @if((int) auth()->id() === (int) $petition->user_id)
                                <div class="bg-white rounded-3 p-4 shadow-sm my-4" style="border:1px solid #eee;">
                                    <div class="fc-box-title">Operations</div>
                                    <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                                        <div style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;"></div>
                                    </div>

                                    <div style="font-size:14px;line-height:1.9;">
                                        <div>
                                            <a class="red" href="{{ lroute('petition.edit', ['slug' => $tr->slug, 'id' => $petition->id]) }}">Edit</a>
                                        </div>

                                        <div class="mt-2">
                                            Download signatures :
                                            <a class="red" href="{{ lroute('petition.download.txt', ['slug' => $tr->slug, 'id' => $petition->id]) }}">TXT</a>
                                            <span class="text-muted"> </span>
                                            <a class="red" href="{{ lroute('petition.download.pdf', ['slug' => $tr->slug, 'id' => $petition->id]) }}">PDF</a>
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
                    <img src="{{ $petitionImg }}" class="img-fluid w-100" alt="petition image large">
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .fc-petition-description ul { list-style: disc !important; padding-left: 22px !important; margin: 0 0 14px 0 !important; }
    .fc-petition-description ol { list-style: decimal !important; padding-left: 22px !important; margin: 0 0 14px 0 !important; }
    .fc-petition-description p { margin: 0 0 12px; }
    .fc-petition-description { font-size: 16px; line-height: 1.75; color: #222; }
    .fc-petition-description li { margin: 0 0 6px 0; }
    .fc-petition-description li>ul, .fc-petition-description li>ol { margin-top: 8px !important; }
    .fc-petition-img { width: 100%; height: 380px; object-fit: cover; border-radius: 8px; display: block; }
    .fc-img-wrap { position: relative; overflow: hidden; border-radius: 10px; background: #f3f3f3; }
    .fc-img-credit { position: absolute; right: 10px; bottom: 10px; background: rgba(0, 0, 0, .55); color: #fff; padding: 4px 8px; font-size: 12px; border-radius: 6px; }
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
    }, { once: true });
});
</script>
@endpush
