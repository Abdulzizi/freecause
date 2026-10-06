@extends('layouts.legacy')

@php
    $isAuthed = auth()->check();

    $petitionTitle = e($tr->title ?? ($petition->title ?? 'Petition'));
    $pageTitle = __('show.page_title', ['title' => $petitionTitle]);

    $petitionImg = $petition->coverUrl();
    $coverVariants = \App\Support\ImageVariants::urls($petition->cover_image);
    $hasCover = false;
    foreach ([$petition->cover_image, $petition->image_url] as $index => $coverPath) {
        if (!$coverPath) {
            continue;
        }
        if (str_starts_with($coverPath, 'http://') || str_starts_with($coverPath, 'https://') || \Illuminate\Support\Facades\Storage::disk('public')->exists($index === 0 ? $coverPath : 'petitions/'.$coverPath)) {
            $hasCover = true;
            break;
        }
    }
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

@push('head')
    @include('partials.hreflang')
@endpush

@section('content')
    <section class="py-12!">
        <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-4!">
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

            <div class="bg-white! shadow-sm! rounded-[.5rem]! p-6! border! border-[#eee]!">
                <div data-confetti-on-load data-confetti-threshold="{{ in_array(true, [$pct>=100, $pct>=75, $pct>=50, $pct>=25], true) ? 1 : 0 }}" hidden></div>
                @if ($pct >= 80 && $pct < 100 && !$hasSigned)
                    <div class="flex! items-center! gap-2! bg-[#fff5f5]! border! border-[#cc1f1f]! text-[#cc1f1f]! rounded-[.5rem]! px-4! py-3! mb-4! text-[14px]! font-semibold!" role="status">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0!">
                            <path d="M12 9v4"></path>
                            <path d="M12 17h.01"></path>
                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        </svg>
                        <span>Only {{ number_format(max(0, $goalTotal - $goalCurrent)) }} signatures to go!</span>
                    </div>
                @endif

                <div class="flex! items-center! justify-between! flex-wrap! gap-2! mb-2!">
                    <h1 class="mb-0! text-[22px]! font-bold! leading-[1.3]! break-words! [overflow-wrap:anywhere]!">
                        {{ $petitionTitle }}
                    </h1>

                    @if (!$hasSigned)
                        <x-ui.button variant="danger" href="#signFormTop">{{ $btnSignNow }}</x-ui.button>
                    @endif
                </div>

                <div class="flex! flex-wrap! items-center! gap-2! mb-4!">
                    @if ($petition->is_active)
                        <span class="inline-flex! items-center! gap-1! rounded-full! bg-[#e6f4ea]! text-[#1e7a34]! border! border-[#bfe3c8]! px-2.5! py-[3px]! text-[12px]! font-semibold!">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0!">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Active
                        </span>
                    @endif
                    @if ($petition->is_featured)
                        <span class="inline-flex! items-center! gap-1! rounded-full! bg-[#fff5f5]! text-[#cc1f1f]! border! border-[#f3c6c6]! px-2.5! py-[3px]! text-[12px]! font-semibold!">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0!">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                            Featured
                        </span>
                    @endif
                    @if ($petition->category?->name)
                        <span class="inline-flex! items-center! rounded-full! bg-[#f1f1f1]! text-[#555]! border! border-[#e0e0e0]! px-2.5! py-[3px]! text-[12px]! font-medium!">
                            {{ $petition->category->name }}
                        </span>
                    @endif
                </div>

                {{-- Share buttons --}}
                <div class="flex! flex-wrap! items-center! gap-2! mb-4!">
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

                <div class="flex! flex-wrap! -mx-3!">
                    <div class="w-full! lg:w-2/3! shrink-0! px-3!">
                        @php $wv = $coverVariants; @endphp
                        <a href="#" class="relative! block! overflow-hidden! rounded-[10px]! bg-[#1a1a1a]! h-[380px]!" data-bs-toggle="modal" data-bs-target="#imgModal">
                            @if ($wv)
                            <picture>
                                <source type="image/webp" srcset="{{ $wv['400'] }} 400w, {{ $wv['800'] }} 800w" sizes="100vw">
                                <img src="{{ $petitionImg }}" alt="" class="absolute! inset-[-15px]! w-[calc(100%+30px)]! h-[calc(100%+30px)]! object-cover! blur-[18px]! opacity-[0.55]! rounded-none!" aria-hidden="true" loading="lazy" decoding="async">
                            </picture>
                            <picture>
                                <source type="image/webp" srcset="{{ $wv['400'] }} 400w, {{ $wv['800'] }} 800w" sizes="(min-width: 992px) 640px, 100vw">
                                <img src="{{ $petitionImg }}" alt="{{ $petitionTitle }}" data-blurup class="fcui-blurup relative! z-10! block! w-full! h-[380px]! object-contain!" loading="eager" fetchpriority="high" decoding="async">
                            </picture>
                            @else
                            <img src="{{ $petitionImg }}" alt="" class="absolute! inset-[-15px]! w-[calc(100%+30px)]! h-[calc(100%+30px)]! object-cover! blur-[18px]! opacity-[0.55]! rounded-none!" aria-hidden="true" loading="lazy" decoding="async">
                            <img src="{{ $petitionImg }}" alt="{{ $petitionTitle }}" data-blurup class="fcui-blurup relative! z-10! block! w-full! h-[380px]! object-contain!" loading="eager" fetchpriority="high" decoding="async">
                            @endif
                            @if ($petitionCredit)
                                <span class="absolute! right-[10px]! bottom-[10px]! rounded-md! bg-black/55! px-2! py-1! text-xs! text-white! text-shadow-[0_1px_2px_rgba(0,0,0,0.6)]!">{{ $petitionCredit }}</span>
                            @endif
                        </a>

                        <div class="mt-4!">
                            <div class="text-base! leading-[1.75]! text-[#222]! [&_p]:mb-3! [&_p]:text-sm! [&_p]:leading-[1.75]! [&_ul]:my-2.5! [&_ul]:mt-0! [&_ul]:mb-3.5! [&_ul]:list-disc! [&_ul]:pl-[22px]! [&_ol]:my-2.5! [&_ol]:mt-0! [&_ol]:mb-3.5! [&_ol]:list-decimal! [&_ol]:pl-[22px]! [&_li]:mb-1.5! [&_li]:text-sm! [&_li]:leading-[1.75]! [&_li>ul]:mt-2! [&_li>ol]:mt-2! [&_h3]:mt-4! [&_h3]:text-base! [&_h3]:font-bold! [&_h4]:mt-3.5! [&_h4]:text-[15px]! [&_h4]:font-bold!">
                                {!! $tr->description !!}
                            </div>
                        </div>

                        <div class="border-t-[2px_dotted_#ccc]! border-b-[1px_dotted_#bbb]! my-6!"></div>

                        <div id="signFormBottom" class="bg-[#f8f9fa]! rounded-[.5rem]! p-6! border! border-[#eee]!">
                            <h2 class="font-bold! text-base!">{{ $boxSignTitle }}</h2>

                            @include('partials.gradient-divider')

                            @if (!$isAuthed)
                                <div class="flex! gap-2! justify-center! flex-wrap! my-4!">
                                    <a class="btn fc-google-btn fc-oauth-btn"
                                        href="{{ lroute('oauth.google', ['flow' => 'petition', 'petition_id' => $petition->id, 'slug' => $tr->slug]) }}">
                                        <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" width="18" height="18" class="w-[18px]! h-[18px]! block! shrink-0!" aria-hidden="true">
                                        {{ $googleContinue }}
                                    </a>
                                </div>

                                <div class="relative! text-center! my-3! before:absolute! before:left-0! before:right-0! before:top-1/2! before:h-px! before:bg-[#ddd]!"><span class="relative! bg-[#f8f9fa]! px-2.5! font-bold!">{{ $orText }}</span></div>

                                <p class="mb-4!">{!! $authHintSplit !!}</p>
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

                        <div class="mt-6!">
                            <h2 class="font-bold! text-base!">{{ $boxShoutbox }}</h2>
                            @include('partials.gradient-divider')

                            <div>
                                @forelse ($shoutbox as $comment)
                                    <div class="flex! items-start! gap-4! py-4! {{ !$loop->last ? 'border-b!' : '' }}">
                                        <div class="grow!">
                                            <div class="flex! items-center! gap-2! flex-wrap!">
                                                @if ($comment->user)
                                                    <a class="font-semibold! text-[15px]! text-[#cc1f1f]!"
                                                        href="{{ lroute('user.profile', ['slug' => \Illuminate\Support\Str::slug($comment->user->name ?? 'user') ?: 'user', 'id' => $comment->user_id]) }}">
                                                        {{ $comment->name ?? $comment->user->name ?? __('sig.anonymous') }}
                                                    </a>
                                                @else
                                                    <strong class="text-[15px]!">{{ $comment->name ?? __('sig.anonymous') }}</strong>
                                                @endif
                                                <span class="text-[#6c757d]! ms-auto! text-xs!">
                                                    {{ optional($comment->created_at)->format('j F Y') }}
                                                </span>
                                            </div>
                                            <p class="mb-0! mt-1! text-[#6c757d]! text-sm!">
                                                "{{ $comment->text }}"
                                            </p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-[#6c757d]! text-sm!">{{ __('sig.no_comments') }}</div>
                                @endforelse

                                @if ($shoutbox->hasPages())
                                    @include('partials.pagination', ['paginator' => $shoutbox])
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="w-full! lg:w-1/3! shrink-0! px-3!">
                        <div id="signFormTop" class="bg-[#f8f9fa]! rounded-[.5rem]! p-6! mb-6! border! border-[#eee]!">
                            <h2 class="font-bold! text-base!">{{ $boxSignTitle }}</h2>
                            @include('partials.gradient-divider')

                            @if (!$isAuthed)
                                <div class="flex! gap-2! justify-center! flex-wrap! my-4!">
                                    <a class="btn fc-google-btn fc-oauth-btn"
                                        href="{{ lroute('oauth.google', ['flow' => 'petition', 'petition_id' => $petition->id, 'slug' => $tr->slug]) }}">
                                        <img src="{{ asset('legacy/images-v2/google-g.svg') }}" alt="" width="18" height="18" class="w-[18px]! h-[18px]! block! shrink-0!" aria-hidden="true">
                                        {{ $googleContinue }}
                                    </a>
                                </div>

                                <div class="relative! text-center! my-3! before:absolute! before:left-0! before:right-0! before:top-1/2! before:h-px! before:bg-[#ddd]!"><span class="relative! bg-[#f8f9fa]! px-2.5! font-bold!">{{ $orText }}</span></div>

                                <p class="mb-4!">{!! $authHintStack !!}</p>
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

                        <div class="bg-white! rounded-[.5rem]! p-6! shadow-sm! mb-6! border! border-[#eee]! transition! duration-150! hover:shadow-md!">
                            <h2 class="font-bold! text-base!">{{ $boxGoal }}</h2>
                            @include('partials.gradient-divider')

                            <div class="h-1.5! bg-[#e9ecef]! rounded-full! overflow-hidden! mt-4!">
                                <div class="h-1.5! bg-[#2ca02c]!" data-progress-bar data-progress-pct="{{ $pct }}" style="width: {{ $pct }}%;"></div>
                            </div>

                            <div class="flex! justify-between! mt-2! text-sm!">
                                <div><span data-countup data-countup-to="{{ $goalCurrent }}">{{ number_format($goalCurrent) }}</span> {{ ltrim(str_replace(number_format($goalCurrent), '', $goalSignaturesText)) }}</div>
                                @php $goalLabelPrefix = trim(str_replace(number_format($goalTotal), '', $goalLabelText)); @endphp
                                <div class="text-[#6c757d]!">{{ $goalLabelPrefix }} <span data-abbr data-abbr-value="{{ $goalTotal }}">{{ number_format($goalTotal) }}</span></div>
                            </div>

                            @if (($recentSignatureCount ?? 0) > 0)
                                <div class="mt-3! flex! items-center! gap-2! text-[13px]! text-[#2ca02c]! font-medium!">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0!">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                    <span>{{ __('show.social_proof', ['count' => number_format($recentSignatureCount)]) }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="bg-white! rounded-[.5rem]! p-6! shadow-sm! mb-6! border! border-[#eee]! transition! duration-150! hover:shadow-md!">
                            <div class="font-bold! text-base! flex! items-center! justify-between!">
                                <span>{{ $boxLatest }}</span>
                            </div>
                            @include('partials.gradient-divider')

                            <div class="text-[13px]! mt-4!">
                                @forelse($latest as $i => $sig)
                                    <div class="py-[6px]! border-b! border-[#f1f1f1]! last:border-b-0!">
                                        <div class="font-bold! mb-0.5!">
                                            {{ $sig->created_at?->format('j F Y') }}
                                        </div>
                                        <div>
                                            <strong>{{ $goalCurrent - $i }}.</strong>
                                            <a href="{{ lroute('petition.signatures', ['slug' => $tr->slug, 'id' => $petition->id]) }}"
                                                class="text-[#cc1f1f]!">{{ $sig->name ?? __('sig.anonymous') }}</a>
                                            <span class="text-[#6c757d]!">|</span>
                                            <span>{{ $sig->text ?? __('sig.default_comment') }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex! flex-col! items-center! justify-center! text-center! py-6! text-[#6c757d]!">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="mb-2! opacity-70!">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <line x1="19" y1="8" x2="19" y2="14"></line>
                                            <line x1="22" y1="11" x2="16" y2="11"></line>
                                        </svg>
                                        <div class="text-sm!">{{ $latestEmpty }}</div>
                                    </div>
                                @endforelse

                                <x-ui.button variant="danger" size="sm" class="mt-2!"
                                    :href="lroute('petition.signatures', ['slug' => $tr->slug, 'id' => $petition->id])">{{ $latestBrowseAll }}</x-ui.button>
                            </div>
                        </div>

                        <div class="bg-white! rounded-[.5rem]! p-6! shadow-sm! mb-6! border! border-[#eee]! transition! duration-150! hover:shadow-md!">
                            <h2 class="font-bold! text-base!">{{ $boxInformation }}</h2>
                            @include('partials.gradient-divider')

                            <div class="mt-4! text-sm! leading-[1.8]!">
                                <div class="flex! justify-between! gap-2!">
                                    <strong>{{ $infoBy }}</strong>
                                    @if ($petition->user)
                                        <a href="{{ lroute('user.profile', ['slug' => \Illuminate\Support\Str::slug($petition->user->name ?? 'user') ?: 'user', 'id' => $petition->user_id]) }}"
                                            class="text-[#cc1f1f]!">
                                            {{ $petition->user->name }}
                                        </a>
                                    @else
                                        <span class="text-[#6c757d]!">Unknown</span>
                                    @endif
                                </div>

                                <div class="flex! justify-between! gap-2!">
                                    <strong>{{ $infoIn }}</strong>
                                    <a href="{{ lroute('petitions.byCategory', ['categorySlug' => $petition->category?->slug ?? '-', 'category' => $petition->category?->id ?? 0]) }}"
                                        class="text-[#cc1f1f]!">
                                        {{ $petition->category?->name ?? '-' }}
                                    </a>
                                </div>

                                <div class="mt-2!">
                                    <strong>{{ $infoTarget }}</strong><br>
                                    {{ $petition->target ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="bg-white! rounded-[.5rem]! p-6! shadow-sm! mb-6! border! border-[#eee]! transition! duration-150! hover:shadow-md!">
                            <h2 class="font-bold! text-base!">{{ $boxTags }}</h2>
                            @include('partials.gradient-divider')
                            <div class="mt-4! text-sm!">
                                @if ($petition->tags)
                                    @foreach (explode(',', $petition->tags) as $tag)
                                        @if (trim($tag))
                                            <span class="badge bg-secondary me-1! mb-1! font-normal! text-[13px]!">{{ trim($tag) }}</span>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="text-[#6c757d]!">{{ $tagsEmpty }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="bg-white! rounded-[.5rem]! p-6! shadow-sm! mb-6! border! border-[#eee]! transition! duration-150! hover:shadow-md!">
                            <h2 class="font-bold! text-base!">{{ $boxEmbed }}</h2>
                            @include('partials.gradient-divider')

                            <div class="mt-4! text-[13px]!">
                                <div class="mb-2!"><strong>{{ $embedDirect }}</strong></div>
                                <input class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25! mb-4!" value="{{ $directLink }}" readonly>

                                <div class="mb-2!"><strong>{{ $embedHtml }}</strong></div>
                                <input class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25! mb-4!"
                                    value='<a href="{{ $directLink }}">{{ $petitionTitle }}</a>' readonly>

                                <div class="mb-2!"><strong>{{ $embedForumNoTitle }}</strong></div>
                                <input class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25! mb-4!" value='[URL="{{ $directLink }}"][/URL]' readonly>

                                <div class="mb-2!"><strong>{{ $embedForumWithTitle }}</strong></div>
                                <input class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25!" value='[URL]{{ $directLink }}[/URL]' readonly>
                            </div>
                        </div>

                        <div class="bg-white! rounded-[.5rem]! p-6! shadow-sm! border! border-[#eee]! transition! duration-150! hover:shadow-md!">
                            <button id="widgetsToggle" class="w-full! border-0! bg-transparent! p-0! inline-flex! items-center! cursor-pointer! focus:outline-none! focus:shadow-none!" type="button"
                                data-bs-toggle="collapse" data-bs-target="#widgetsBody" aria-expanded="false"
                                aria-controls="widgetsBody">
                                <span class="font-bold! text-base! mb-0! flex! items-center! gap-2!">
                                    {{ $boxWidgets }}
                                    <i class="fa fa-angle-down leading-none! cursor-pointer" aria-hidden="true"></i>
                                </span>
                            </button>

                            @include('partials.gradient-divider')

                            <div id="widgetsBody" class="collapse">
                                <div class="mt-4!">
                                    @foreach (['728x90', '468x60', '336x280', '125x125'] as $widgetType)
                                        @php
                                            $widgetCode = '<script src="'.url("/widgets/pbadge/{$petition->id}/{$widgetType}").'"></script>';
                                        @endphp
                                        <div class="mb-2!"><strong>{{ str_replace('x', '×', $widgetType) }}</strong></div>
                                        <textarea class="block! w-full! px-3! py-1.5! text-base! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25! mb-4!" rows="3" readonly>{{ $widgetCode }}</textarea>
                                        <div class="mb-6! overflow-auto">
                                            <img src="{{ asset("legacy/images/widget-pbadge-{$widgetType}-example.png") }}"
                                                alt="{{ $widgetType }} petition widget preview" loading="lazy" decoding="async">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        @auth
                            @if ((int) auth()->id() === (int) $petition->user_id)
                                <div class="bg-white! rounded-[.5rem]! p-6! shadow-sm! my-6! border! border-[#eee]! transition! duration-150! hover:shadow-md!">
                                    <h2 class="font-bold! text-base!">{{ __('show.operations') }}</h2>
                                    @include('partials.gradient-divider')

                                    <div class="text-sm! leading-[1.9]!">
                                        <div>
                                            <a class="text-[#cc1f1f]!"
                                                href="{{ lroute('petition.edit', ['slug' => $tr->slug, 'id' => $petition->id]) }}">{{ __('show.edit') }}</a>
                                        </div>

                                        <div class="mt-2!">
                                            {{ __('show.download_signatures') }} :
                                            <a class="text-[#cc1f1f]!"
                                                href="{{ lroute('petition.download.txt', ['slug' => $tr->slug, 'id' => $petition->id]) }}">TXT</a>
                                            <span class="text-[#6c757d]!"> </span>
                                            <a class="text-[#cc1f1f]!"
                                                href="{{ lroute('petition.download.pdf', ['slug' => $tr->slug, 'id' => $petition->id]) }}">PDF</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endauth

                        <x-ad-unit placement="petition_sidebar" class="mt-6!" min-height="250px" />

                    </div>
                </div>
            </div>

            @if (($related ?? collect())->isNotEmpty())
                <div class="mt-10!">
                    <h2 class="text-[18px]! font-bold! mb-4!">{{ __('show.related') }}</h2>
                    <div class="flex! flex-wrap! -mx-3!">
                        @foreach ($related as $rel)
                            <div class="w-full! md:w-1/3! shrink-0! px-3! mb-6!">
                                <a href="{{ lroute('petition.show', ['slug' => $rel->tr_slug, 'id' => $rel->id]) }}"
                                   class="block! h-full! no-underline! bg-white! rounded-[.5rem]! border! border-[#eee]! shadow-sm! overflow-hidden! transition! duration-150! hover:-translate-y-0.5! hover:shadow-lg!">
                                    <div class="p-4!">
                                        <div class="text-[#222]! font-semibold! text-sm! leading-snug! break-words! [overflow-wrap:anywhere]!">{{ $rel->tr_title }}</div>
                                        <div class="mt-2! text-[13px]! text-[#6c757d]!">
                                            <span data-countup data-countup-to="{{ (int) ($rel->signature_count ?? 0) }}">{{ number_format($rel->signature_count ?? 0) }}</span> {{ __('list.signatures') }}
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <button type="button" class="fcui-scrolltop" data-scroll-top aria-label="Back to top">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="12" y1="19" x2="12" y2="5"></line>
                    <polyline points="5 12 12 5 19 12"></polyline>
                </svg>
            </button>

        </div>
    </section>

    @if (!$hasSigned)
        <div class="fcui-sticky-signbar">
            <a href="#signFormTop" class="btn btn-danger bg-[#cc1f1f]! border-[#cc1f1f]! rounded-[6px]! px-[20px]! py-[10px]! font-semibold! text-[14px]! block! text-center! w-full! text-white! no-underline!">{{ $btnSignNow }}</a>
        </div>
    @endif

    <div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content p-2">
                <div class="modal-body p-0 position-relative">
                    <button type="button" class="btn-close position-absolute right-[10px]! top-[10px]! z-[2]!"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                    <img src="{{ $petitionImg }}" class="img-fluid w-100" loading="lazy" decoding="async" alt="{{ $petitionTitle }}">
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

@push('head')
    @if ($hasCover)
        @if ($coverVariants)
            <link rel="preload" as="image" href="{{ $coverVariants['800'] }}" type="image/webp"
                imagesrcset="{{ $coverVariants['400'] }} 400w, {{ $coverVariants['800'] }} 800w"
                imagesizes="(min-width: 992px) 640px, 100vw" fetchpriority="high">
        @else
            <link rel="preload" as="image" href="{{ $petitionImg }}" fetchpriority="high">
        @endif
    @endif
@endpush

@push('scripts')
    <script src="{{ asset('legacy/js-v2/petition-show.js') }}"></script>
@endpush
