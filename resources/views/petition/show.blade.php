@extends('layouts.legacy')

@php
    $isAuthed = auth()->check();

    $petitionTitle = e($tr->title ?? ($petition->title ?? 'Petition'));
    $pageTitle = __('show.page_title', ['title' => $petitionTitle]);

    $petitionImg = $petition->coverUrl();
    $petitionCredit = $petition->image_credit ?? '';

    $btnSignNow = __('show.btn_sign_now');
    $boxSignTitle = __('show.box_sign_title');
    $googleContinue = __('show.google_continue');
    $orText = __('show.or');

    $loginUrl = lroute('login');

    $authHintSplit = __('show.auth_hint_split', ['login_url' => $loginUrl]);
    $authHintStack = __('show.auth_hint_stack', ['login_url' => $loginUrl]);

    $boxShoutbox = __('show.box_shoutbox');

    $boxGoal = __('show.box_goal');
    $goalSignaturesText = __('show.goal_signatures', ['count' => number_format($goalCurrent)]);
    $goalLabelText = __('show.goal_label', ['count' => number_format($goalTotal)]);

    $boxLatest = __('show.box_latest');
    $latestEmpty = __('show.latest_empty');
    $latestBrowseAll = __('show.latest_browse_all');

    $boxInformation = __('show.box_information');
    $infoBy = __('show.info_by');
    $infoIn = __('show.info_in');
    $infoTarget = __('show.info_target');

    $boxTags = __('show.box_tags');
    $tagsEmpty = __('show.tags_empty');

    $boxEmbed = __('show.box_embed');
    $embedDirect = __('show.embed_direct');
    $embedHtml = __('show.embed_html');
    $embedForumNoTitle = __('show.embed_forum_no_title');
    $embedForumWithTitle = __('show.embed_forum_with_title');

    $boxWidgets = __('show.box_widgets');
@endphp

@section('title', $pageTitle)
@section('og_title', $petitionTitle)
@section('og_description', \Illuminate\Support\Str::limit(strip_tags($tr->description ?? ''), 200))
@section('og_image', $petitionImg)
@section('og_url', request()->url())

@section('content')
    <section class="py-5">
        <div class="container">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb text-[13px]! mb-0!">
                    <li class="breadcrumb-item">
                        <a href="{{ lroute('home') }}" class="text-[#cc1f1f]!">{{ __('show.breadcrumb_home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ lroute('petitions.index') }}" class="text-[#cc1f1f]!">{{ __('show.breadcrumb_petitions') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ \Illuminate\Support\Str::limit($petitionTitle, 55) }}
                    </li>
                </ol>
            </nav>

            <div class="bg-white shadow-sm rounded-3 p-4 border! border-[#eee]!">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h1 class="mb-0 text-[22px]! font-bold! leading-[1.3]!">
                        {{ $petitionTitle }}
                    </h1>

                    @if (!$hasSigned)
                        <a href="#signFormTop" class="btn btn-danger rounded-md! bg-[#cc1f1f]! border-[#cc1f1f]! px-[14px]! py-[6px]! font-semibold!">{{ $btnSignNow }}</a>
                    @endif
                </div>

                {{-- Share buttons --}}
                <div class="flex! flex-wrap! items-center! gap-2! mb-3">
                    <span class="text-[13px]! font-semibold! text-[#555]!">{{ __('show.share') }}:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                       target="_blank" rel="noopener noreferrer" class="inline-flex! items-center! gap-[5px]! rounded! px-3! py-[5px]! text-[13px]! font-medium! no-underline! border-0! cursor-pointer! transition-opacity! duration-150! hover:opacity-85! hover:no-underline! bg-[#1877f2]! text-white!"
                       aria-label="Share on Facebook">
                        <i class="fa fa-facebook"></i> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($petitionTitle) }}"
                       target="_blank" rel="noopener noreferrer" class="inline-flex! items-center! gap-[5px]! rounded! px-3! py-[5px]! text-[13px]! font-medium! no-underline! border-0! cursor-pointer! transition-opacity! duration-150! hover:opacity-85! hover:no-underline! bg-black! text-white!"
                       aria-label="Share on Twitter">
                        <i class="fa fa-twitter"></i> Twitter
                    </a>
                    <a href="https://wa.me/?text={{ urlencode($petitionTitle.' '.request()->url()) }}"
                       target="_blank" rel="noopener noreferrer" class="inline-flex! items-center! gap-[5px]! rounded! px-3! py-[5px]! text-[13px]! font-medium! no-underline! border-0! cursor-pointer! transition-opacity! duration-150! hover:opacity-85! hover:no-underline! bg-[#25d366]! text-white!"
                       aria-label="Share on WhatsApp">
                        <i class="fa fa-whatsapp"></i> WhatsApp
                    </a>
                    <button type="button" id="copyPetitionLink" class="fc-share-copy inline-flex! items-center! gap-[5px]! rounded! px-3! py-[5px]! text-[13px]! font-medium! no-underline! border-0! cursor-pointer! transition-opacity! duration-150! hover:opacity-85! hover:no-underline!"
                            data-url="{{ request()->url() }}"
                            data-copied-label="{{ __('show.link_copied') }}"
                            aria-label="Copy link">
                        <i class="fa fa-link"></i> {{ __('show.copy_link') }}
                    </button>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <a href="#" class="relative! block! overflow-hidden! rounded-[10px]! bg-[#1a1a1a]! h-[380px]!" data-bs-toggle="modal" data-bs-target="#imgModal">
                            <img src="{{ $petitionImg }}" alt="" class="absolute! inset-[-15px]! w-[calc(100%+30px)]! h-[calc(100%+30px)]! object-cover! blur-[18px]! opacity-[0.55]! rounded-none!" aria-hidden="true">
                            <img src="{{ $petitionImg }}" alt="{{ $petitionTitle }}" class="relative! z-10! block! w-full! h-[380px]! object-contain!" loading="lazy">
                            @if ($petitionCredit)
                                <span class="absolute! right-[10px]! bottom-[10px]! rounded-md! bg-black/55! px-2! py-1! text-xs! text-white! text-shadow-[0_1px_2px_rgba(0,0,0,0.6)]!">{{ $petitionCredit }}</span>
                            @endif
                        </a>

                        <div class="mt-3">
                            <div class="text-base! leading-[1.75]! text-[#222]! [&_p]:mb-3! [&_p]:text-sm! [&_p]:leading-[1.75]! [&_ul]:my-2.5! [&_ul]:mt-0! [&_ul]:mb-3.5! [&_ul]:list-disc! [&_ul]:pl-[22px]! [&_ol]:my-2.5! [&_ol]:mt-0! [&_ol]:mb-3.5! [&_ol]:list-decimal! [&_ol]:pl-[22px]! [&_li]:mb-1.5! [&_li]:text-sm! [&_li]:leading-[1.75]! [&_li>ul]:mt-2! [&_li>ol]:mt-2! [&_h3]:mt-4! [&_h3]:text-base! [&_h3]:font-bold! [&_h4]:mt-3.5! [&_h4]:text-[15px]! [&_h4]:font-bold!">
                                {!! $tr->description !!}
                            </div>
                        </div>

                        <div class="border-t-[2px_dotted_#ccc]! border-b-[1px_dotted_#bbb]! my-4"></div>

                        <div id="signFormBottom" class="bg-light rounded-3 p-4 border! border-[#eee]!">
                            <h2 class="font-bold! text-base!">{{ $boxSignTitle }}</h2>

                            @include('partials.gradient-divider')

                            @if (!$isAuthed)
                                <div class="d-flex gap-2 justify-content-center flex-wrap my-3">
                                    <a class="btn inline-flex! items-center! gap-[10px]! rounded-md! border! border-[#ddd]! bg-white! px-4! py-2.5! text-sm! font-medium! text-[#444]! no-underline! hover:bg-[#f8f9fa]! hover:text-[#222]! hover:no-underline!"
                                        href="{{ lroute('oauth.google', ['flow' => 'petition', 'petition_id' => $petition->id, 'slug' => $tr->slug]) }}">
                                        <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" class="w-[18px]! h-[18px]! block!" aria-hidden="true">
                                        {{ $googleContinue }}
                                    </a>
                                </div>

                                <div class="relative! text-center! my-3! before:absolute! before:left-0! before:right-0! before:top-1/2! before:h-px! before:bg-[#ddd]!"><span class="relative! bg-[#f8f9fa]! px-2.5! font-bold!">{{ $orText }}</span></div>

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
                            <h2 class="font-bold! text-base!">{{ $boxShoutbox }}</h2>
                            @include('partials.gradient-divider')

                            <div>
                                @forelse ($shoutbox as $comment)
                                    <div class="d-flex align-items-start gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                @if ($comment->user)
                                                    <a class="font-semibold! text-[15px]! text-[#cc1f1f]!"
                                                        href="{{ lroute('user.profile', ['slug' => \Illuminate\Support\Str::slug($comment->user->name ?? 'user') ?: 'user', 'id' => $comment->user_id]) }}">
                                                        {{ $comment->name ?? $comment->user->name ?? __('sig.anonymous') }}
                                                    </a>
                                                @else
                                                    <strong class="text-[15px]!">{{ $comment->name ?? __('sig.anonymous') }}</strong>
                                                @endif
                                                <span class="text-muted ms-auto text-xs!">
                                                    {{ optional($comment->created_at)->format('j F Y') }}
                                                </span>
                                            </div>
                                            <p class="mb-0 mt-1 text-muted text-sm!">
                                                "{{ $comment->text }}"
                                            </p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted text-sm!">{{ __('sig.no_comments') }}</div>
                                @endforelse

                                @if ($shoutbox->hasPages())
                                    @include('partials.pagination', ['paginator' => $shoutbox])
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div id="signFormTop" class="bg-light rounded-3 p-4 mb-4 border! border-[#eee]!">
                            <h2 class="font-bold! text-base!">{{ $boxSignTitle }}</h2>
                            @include('partials.gradient-divider')

                            @if (!$isAuthed)
                                <div class="d-flex gap-2 justify-content-center flex-wrap my-3">
                                    <a class="btn inline-flex! items-center! gap-[10px]! rounded-md! border! border-[#ddd]! bg-white! px-4! py-2.5! text-sm! font-medium! text-[#444]! no-underline! hover:bg-[#f8f9fa]! hover:text-[#222]! hover:no-underline!"
                                        href="{{ lroute('oauth.google', ['flow' => 'petition', 'petition_id' => $petition->id, 'slug' => $tr->slug]) }}">
                                        <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" class="w-[18px]! h-[18px]! block!" aria-hidden="true">
                                        {{ $googleContinue }}
                                    </a>
                                </div>

                                <div class="relative! text-center! my-3! before:absolute! before:left-0! before:right-0! before:top-1/2! before:h-px! before:bg-[#ddd]!"><span class="relative! bg-[#f8f9fa]! px-2.5! font-bold!">{{ $orText }}</span></div>

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

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4 border! border-[#eee]!">
                            <h2 class="font-bold! text-base!">{{ $boxGoal }}</h2>
                            @include('partials.gradient-divider')

                            <div class="h-1.5! bg-[#e9ecef]! rounded-full! overflow-hidden! mt-3">
                                <div class="h-1.5! bg-[#2ca02c]!" style="width: {{ $pct }}%;"></div>
                            </div>

                            <div class="d-flex justify-content-between mt-2 text-sm!">
                                <div>{{ $goalSignaturesText }}</div>
                                <div class="text-muted">{{ $goalLabelText }}</div>
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4 border! border-[#eee]!">
                            <div class="font-bold! text-base! d-flex align-items-center justify-content-between">
                                <span>{{ $boxLatest }}</span>
                            </div>
                            @include('partials.gradient-divider')

                            <div class="text-[13px]! mt-3">
                                @forelse($latest as $i => $sig)
                                    <div class="py-[6px]! border-b! border-[#f1f1f1]! last:border-b-0!">
                                        <div class="font-bold! mb-0.5!">
                                            {{ $sig->created_at?->format('j F Y') }}
                                        </div>
                                        <div>
                                            <strong>{{ $goalCurrent - $i }}.</strong>
                                            <a href="{{ lroute('petition.signatures', ['slug' => $tr->slug, 'id' => $petition->id]) }}"
                                                class="text-[#cc1f1f]!">{{ $sig->name ?? __('sig.anonymous') }}</a>
                                            <span class="text-muted">|</span>
                                            <span>{{ $sig->text ?? __('sig.default_comment') }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted text-sm!">{{ $latestEmpty }}</div>
                                @endforelse

                                <a href="{{ lroute('petition.signatures', ['slug' => $tr->slug, 'id' => $petition->id]) }}"
                                    class="btn btn-sm btn-danger mt-2">{{ $latestBrowseAll }}</a>
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4 border! border-[#eee]!">
                            <h2 class="font-bold! text-base!">{{ $boxInformation }}</h2>
                            @include('partials.gradient-divider')

                            <div class="mt-3 text-sm! leading-[1.8]!">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong>{{ $infoBy }}</strong>
                                    @if ($petition->user)
                                        <a href="{{ lroute('user.profile', ['slug' => \Illuminate\Support\Str::slug($petition->user->name ?? 'user') ?: 'user', 'id' => $petition->user_id]) }}"
                                            class="text-[#cc1f1f]!">
                                            {{ $petition->user->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between gap-2">
                                    <strong>{{ $infoIn }}</strong>
                                    <a href="{{ lroute('petitions.byCategory', ['categorySlug' => $petition->category?->slug ?? '-', 'category' => $petition->category?->id ?? 0]) }}"
                                        class="text-[#cc1f1f]!">
                                        {{ $petition->category?->name ?? '-' }}
                                    </a>
                                </div>

                                <div class="mt-2">
                                    <strong>{{ $infoTarget }}</strong><br>
                                    {{ $petition->target ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4 border! border-[#eee]!">
                            <h2 class="font-bold! text-base!">{{ $boxTags }}</h2>
                            @include('partials.gradient-divider')
                            <div class="mt-3 text-sm!">
                                @if ($petition->tags)
                                    @foreach (explode(',', $petition->tags) as $tag)
                                        @if (trim($tag))
                                            <span class="badge bg-secondary me-1 mb-1 font-normal! text-[13px]!">{{ trim($tag) }}</span>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="text-muted">{{ $tagsEmpty }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4 shadow-sm mb-4 border! border-[#eee]!">
                            <h2 class="font-bold! text-base!">{{ $boxEmbed }}</h2>
                            @include('partials.gradient-divider')

                            <div class="mt-3 text-[13px]!">
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

                        <div class="bg-white rounded-3 p-4 shadow-sm border! border-[#eee]!">
                            <button id="widgetsToggle" class="w-full! border-0! bg-transparent! p-0! inline-flex! items-center! cursor-pointer! focus:outline-none! focus:shadow-none!" type="button"
                                data-bs-toggle="collapse" data-bs-target="#widgetsBody" aria-expanded="false"
                                aria-controls="widgetsBody">
                                <span class="font-bold! text-base! mb-0 d-flex align-items-center gap-2">
                                    {{ $boxWidgets }}
                                    <i class="fa fa-angle-down leading-none! cursor-pointer" aria-hidden="true"></i>
                                </span>
                            </button>

                            @include('partials.gradient-divider')

                            <div id="widgetsBody" class="collapse">
                                <div class="mt-3">
                                    @foreach (['728x90', '468x60', '336x280', '125x125'] as $widgetType)
                                        @php
                                            $widgetCode = '<script src="'.url("/widgets/pbadge/{$petition->id}/{$widgetType}").'"></script>';
                                        @endphp
                                        <div class="mb-2"><strong>{{ str_replace('x', '×', $widgetType) }}</strong></div>
                                        <textarea class="form-control mb-3" rows="3" readonly>{{ $widgetCode }}</textarea>
                                        <div class="mb-4 overflow-auto">
                                            <img src="{{ asset("legacy/images/widget-pbadge-{$widgetType}-example.png") }}"
                                                alt="{{ $widgetType }} petition widget preview">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        @auth
                            @if ((int) auth()->id() === (int) $petition->user_id)
                                <div class="bg-white rounded-3 p-4 shadow-sm my-4 border! border-[#eee]!">
                                    <h2 class="font-bold! text-base!">{{ __('show.operations') }}</h2>
                                    @include('partials.gradient-divider')

                                    <div class="text-sm! leading-[1.9]!">
                                        <div>
                                            <a class="text-[#cc1f1f]!"
                                                href="{{ lroute('petition.edit', ['slug' => $tr->slug, 'id' => $petition->id]) }}">{{ __('show.edit') }}</a>
                                        </div>

                                        <div class="mt-2">
                                            {{ __('show.download_signatures') }} :
                                            <a class="text-[#cc1f1f]!"
                                                href="{{ lroute('petition.download.txt', ['slug' => $tr->slug, 'id' => $petition->id]) }}">TXT</a>
                                            <span class="text-muted"> </span>
                                            <a class="text-[#cc1f1f]!"
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
                    <button type="button" class="btn-close position-absolute right-[10px]! top-[10px]! z-[2]!"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                    <img src="{{ $petitionImg }}" class="img-fluid w-100" loading="lazy" alt="{{ $petitionTitle }}">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CreativeWork",
  "name": {{ Illuminate\Support\Js::from($petitionTitle) }},
  "description": {{ Illuminate\Support\Js::from(\Illuminate\Support\Str::limit(strip_tags($tr->description ?? ''), 200)) }},
  "url": "{{ request()->url() }}",
  "image": "{{ $petitionImg }}",
  "author": {
    "@type": "Person",
    "name": {{ Illuminate\Support\Js::from($petition->user->name ?? 'Unknown') }}
  },
  "interactionStatistic": {
    "@type": "InteractionCounter",
    "interactionType": "https://schema.org/SignAction",
    "userInteractionCount": {{ (int) $petition->signature_count }}
  }
}
</script>
@endpush

@push('scripts')
    <script src="{{ asset('legacy/js-v2/petition-show.js') }}"></script>
@endpush
