@extends('layouts.legacy')

@php
    $isCreated = ($mode ?? 'signed') === 'created';
    $requiresVerification = ! $isCreated && ! ($verified ?? true);

    $pageTitle = $isCreated
        ? __('thanks.title_created')
        : __('thanks.title_signed');

    $h1Text = $isCreated
        ? __('thanks.h1_created')
        : __('thanks.h1_signed');

    $pText = $isCreated
        ? __('thanks.p_created')
        : __('thanks.p_signed');

    $suggestionsTitle = __('thanks.suggestions');
    $suggestionsEmpty = __('thanks.no_suggestions');
    $inviteBtnText = __('thanks.invite');

    $petitionUrl = isset($tr) && $tr ? lroute('petition.show', ['slug' => $tr->slug, 'id' => $petition->id]) : '#';
    $petitionTitle = $tr->title ?? __('thanks.petition_fallback');
    $shareEmailUrl = 'mailto:?'.http_build_query([
        'subject' => $petitionTitle,
        'body' => $petitionTitle."\r\n\r\n".$petitionUrl,
    ], '', '&', PHP_QUERY_RFC3986);
@endphp

@section('title', $pageTitle)

@section('content')
    <div class="mb-4 text-center hidden lg:block">
        <x-ads.slot name="leaderboard" />
    </div>
    <section class="py-14 md:py-24">
        <x-ui.container>
            <div class="bg-white shadow-elev-1 rounded-md p-4 sm:p-6 max-w-[820px] mx-auto">

                <h1 class="mb-4 text-2xl">
                    {{ $h1Text }}
                </h1>

                @if ($reviewRequired ?? false)
                    <div class="mb-4 rounded-md border border-warning/30 bg-warning-soft px-4 py-3 text-sm text-warning" role="status">
                        {{ __('petition.review_required') }}
                    </div>
                @endif

                <div class="mb-4">
                    <a class="text-brand" href="{{ $petitionUrl }}">
                        {{ $petitionTitle }}
                    </a>
                </div>

                @if ($isCreated || $requiresVerification)
                    <p class="mb-6 text-sm">
                        {{ $pText }}
                    </p>
                @endif

                @if (!empty($claim))
                    <div class="mt-6">
                        @if ($canClaim)
                            <div class="p-4 rounded-sm border border-border bg-surface-2">
                                <h2 class="mb-4 text-lg">{{ __('sign.claim_title') }}</h2>
                                <form method="POST" action="{{ lroute('petition.claim', ['slug' => $tr->slug ?? '', 'id' => $petition->id]) }}">
                                    @csrf
                                    <div class="mb-4">
                                        <input class="block w-full max-md:min-h-12 px-3 py-1.5 text-base leading-normal text-ink bg-white border border-border rounded-md appearance-none focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/25 @error('password') is-invalid @enderror" type="password" name="password" minlength="8" placeholder="{{ __('sign.claim_password') }}">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <x-ui.button type="submit" variant="danger" class="max-md:min-h-12">{{ __('sign.claim_btn') }}</x-ui.button>
                                </form>
                                <p class="mt-4 mb-0 text-muted text-sm">
                                    {{ __('sign.claim_verify_note') }}
                                </p>
                            </div>
                        @else
                            <p class="mb-0 text-muted text-sm">
                                {{ __('sign.claim_existing') }}
                            </p>
                        @endif
                    </div>
                @endif

                <h2 class="mt-10 mb-4 text-xl">
                    {{ $suggestionsTitle }}
                </h2>

                @if (($mode ?? 'signed') === 'signed')
                    <div class="mb-8 rounded-sm border border-border bg-surface-2 p-4">
                        <h2 class="mb-3 text-lg">Help {{ $tr->title ?? '' }} reach {{ number_format($nextMilestone ?? 50) }}</h2>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl ?? $petitionUrl) }}"
                               target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 rounded-sm px-3 py-1 text-sm font-medium no-underline bg-facebook text-white">Facebook</a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl ?? $petitionUrl) }}&text={{ urlencode($petitionTitle) }}"
                               target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 rounded-sm px-3 py-1 text-sm font-medium no-underline bg-dark text-on-dark">X</a>
                            <a href="https://wa.me/?text={{ urlencode($petitionTitle.' '.($shareUrl ?? $petitionUrl)) }}"
                               target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 rounded-sm px-3 py-1 text-sm font-medium no-underline bg-whatsapp text-white">WhatsApp</a>
                        </div>
                    </div>
                @endif
                <div class="mb-8">
                    @forelse($suggestions as $p)
                        @if ($loop->index > 0 && $loop->index % 4 === 0)
                            <x-ads.infeed name="native" />
                        @endif
                        @php
                            $sUrl = !empty($p->tr_slug)
                                ? lroute('petition.show', ['slug' => $p->tr_slug, 'id' => $p->id])
                                : '#';
                        @endphp

                        <div class="mb-2.5">
                            <a class="flex max-md:min-h-12 items-center px-3.5 py-2.5 border border-brand-border rounded-md bg-brand-soft text-brand no-underline transition duration-150 hover:bg-brand hover:text-white hover:border-brand"
                                href="{{ $sUrl }}">
                                {{ $p->tr_title ?? __('thanks.petition_fallback') }}
                            </a>
                        </div>
                    @empty
                        <div class="text-muted text-sm">
                            {{ $suggestionsEmpty }}
                        </div>
                    @endforelse
                </div>

                <div class="mb-4 text-center">
                    <x-ads.slot name="rectangle" />
                </div>
                <x-ui.button variant="danger"
                    :href="$shareEmailUrl"
                    data-share-petition
                    data-share-title="{{ $petitionTitle }}"
                    data-share-url="{{ $petitionUrl }}"
                    aria-label="{{ $inviteBtnText }}"
                    class="hidden max-md:min-h-12 md:inline-flex">
                    {{ $inviteBtnText }}
                </x-ui.button>

                <x-ui.button variant="danger" type="button"
                    class="w-full max-md:min-h-12 md:hidden"
                    data-sheet-open="thanks-share"
                    aria-haspopup="dialog">
                    {{ $inviteBtnText }}
                </x-ui.button>
            </div>
        </x-ui.container>
    </section>

    <x-ui.action-sheet id="thanks-share" :title="__('show.share')">
        <a class="fcui-sheet-list__item" href="{{ $shareEmailUrl }}"
            data-share-petition
            data-share-title="{{ $petitionTitle }}"
            data-share-url="{{ $petitionUrl }}">
            <x-lucide-send aria-hidden="true" />
            <span>{{ $inviteBtnText }}</span>
        </a>
        <a class="fcui-sheet-list__item" href="{{ $petitionUrl }}">
            <x-lucide-arrow-right aria-hidden="true" />
            <span>{{ __('common.view') }}</span>
        </a>
        <a class="fcui-sheet-list__item" href="{{ url('/'.app()->getLocale()) }}">
            <x-lucide-home aria-hidden="true" />
            <span>{{ __('errors.home') }}</span>
        </a>
    </x-ui.action-sheet>

    <div class="my-4 text-center">
        <x-ads.slot name="banner468" />
    </div>
@endsection
