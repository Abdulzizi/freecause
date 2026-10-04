@extends('layouts.legacy')

@php
    $petitionTitle = e($tr->title ?? __('common.petition'));
    $shortDesc = \Illuminate\Support\Str::limit(strip_tags($tr->description ?? ''), 200);
    $petitionUrl = lroute('petition.show', ['slug' => $tr->slug, 'id' => $petition->id]);
    $signUrl = lroute('petition.sign.page', ['slug' => $tr->slug, 'id' => $petition->id]);
    $coverImg = $petition->coverUrl();
@endphp

@section('title', 'Signatures: ' . $petitionTitle)

@section('content')
    <section class="py-16!">
        <div class="container">

            <div class="bg-white shadow-sm rounded-3 p-4 mb-4 border border-[#eee]!">
                <div class="row align-items-center g-4">

                    @php $wv = \App\Support\ImageVariants::urls($petition->cover_image); @endphp
                    <div class="col-md-3 col-sm-4">
                        <a href="{{ $petitionUrl }}" class="rounded-lg! overflow-hidden! block! h-40! bg-[#1a1a1a]! relative!">
                            @if ($wv)
                            <picture>
                                <source type="image/webp" srcset="{{ $wv['400'] }} 400w, {{ $wv['800'] }} 800w" sizes="100vw">
                                <img src="{{ $coverImg }}" alt="" aria-hidden="true"
                                    class="absolute! -inset-2.5! w-[calc(100%+20px)]! h-[calc(100%+20px)]! object-cover! blur-[14px]! opacity-[0.55]!" loading="lazy" decoding="async">
                            </picture>
                            <picture>
                                <source type="image/webp" srcset="{{ $wv['400'] }} 400w, {{ $wv['800'] }} 800w" sizes="(min-width: 768px) 400px, 100vw">
                                <img src="{{ $coverImg }}" alt="{{ $petitionTitle }}"
                                    class="relative! z-[1]! w-full! h-40! object-contain!" loading="eager" decoding="async">
                            </picture>
                            @else
                            <img src="{{ $coverImg }}" alt="" aria-hidden="true"
                                class="absolute! -inset-2.5! w-[calc(100%+20px)]! h-[calc(100%+20px)]! object-cover! blur-[14px]! opacity-[0.55]!" loading="lazy" decoding="async">
                            <img src="{{ $coverImg }}" alt="{{ $petitionTitle }}"
                                class="relative! z-[1]! w-full! h-40! object-contain!" loading="eager" decoding="async">
                            @endif
                        </a>
                    </div>

                    <div class="col-md-6 col-sm-8">
                        <h1 class="mb-2 text-2xl! md:text-[19px]! font-bold! leading-[1.35]!">
                            <a href="{{ $petitionUrl }}" class="text-dark text-decoration-none">
                                {{ $petitionTitle }}
                            </a>
                        </h1>

                        <p class="text-muted mb-3 text-sm! leading-[1.6]!">
                            {{ $shortDesc }}
                            <a href="{{ $petitionUrl }}" class="text-[#cc1f1f]! ms-1!">{{ __('sig.continue') }}</a>
                        </p>

                        <div class="h-1.5! bg-[#e9ecef]! rounded-full! overflow-hidden! mb-2">
                            <div class="h-1.5! bg-[#2ca02c]!" style="width: {{ $pct }}%;"></div>
                        </div>
                        <div class="d-flex justify-content-between text-[13px]! text-[#666]!">
                            <span><strong>{{ number_format($goalCurrent) }}</strong> {{ __('sig.signatures') }}</span>
                            <span>{{ __('sig.goal') }} {{ number_format($goalTotal) }}</span>
                        </div>
                    </div>

                    <div class="col-md-3 text-md-end">
                        <x-ui.button variant="danger" :href="$signUrl">
                            {{ __('sig.sign_link') }}
                        </x-ui.button>
                    </div>

                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4 border border-[#eee]!">

                <div class="font-bold! text-base! mb-3">
                    {{ __('sig.all_signatures') }}
                    <span class="text-muted fw-normal text-sm!">
                        ({{ number_format($goalCurrent) }} {{ __('sig.total') }})
                    </span>
                </div>

                <div class="h-0.5! bg-[#e9ecef]! relative! mb-[22px]!">
                    <div
                        class="h-0.5! w-full! bg-[linear-gradient(to_right,#000,#f00)]! absolute! left-0! top-0!">
                    </div>
                </div>

                @forelse($signatures as $sig)
                    <div class="d-flex align-items-start gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="flex-shrink-0 text-muted text-[13px]! min-w-[32px]! pt-0.5!">
                            {{ ($signatures->currentPage() - 1) * $signatures->perPage() + $loop->iteration }}.
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <strong class="text-[15px]!">{{ $sig->name ?? __('sig.anonymous') }}</strong>
                                @if ($sig->user?->city)
                                    <span class="text-muted text-[13px]!">
                                        · {{ $sig->user->city }}
                                    </span>
                                @endif
                                <span class="text-muted ms-auto text-xs!">
                                    {{ optional($sig->created_at)->format('j F Y') }}
                                </span>
                            </div>
                            @if (!empty($sig->text) && $sig->text !== __('sig.default_comment'))
                                <p class="mb-0 mt-1 text-muted text-sm!">
                                    "{{ $sig->text }}"
                                </p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-muted py-4 text-center text-sm!">
                        {{ __('sig.no_signatures') }}
                    </div>
                @endforelse

                @include('partials.pagination', ['paginator' => $signatures])

            </div>

        </div>
    </section>
@endsection
