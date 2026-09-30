@extends('layouts.legacy')

@php
    $isCreated = ($mode ?? 'signed') === 'created';
    $requiresVerification = ! $isCreated && (string) ($status ?? '0') === '0';

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
@endphp

@section('title', $pageTitle)

@section('content')
    <section class="py-16!">
        <div class="container">
            <div class="bg-white shadow-sm rounded-3 p-4 border border-[#eee]! max-w-[820px]! mx-auto!">

                <h1 class="mb-3 text-2xl! md:text-[28px]!">
                    {{ $h1Text }}
                </h1>

                <div class="mb-3">
                    <a class="text-[#cc1f1f]!" href="{{ $petitionUrl }}">
                        {{ $tr->title ?? __('thanks.petition_fallback') }}
                    </a>
                </div>

                @if ($isCreated || $requiresVerification)
                    <p class="mb-4 text-[15px]!">
                        {{ $pText }}
                    </p>
                @endif

                @if (!empty($claim))
                    <div class="mt-4">
                        @if ($canClaim)
                            <div class="p-3 rounded border bg-light">
                                <h2 class="mb-3 text-[1.1rem]! md:text-lg!">{{ __('sign.claim_title') }}</h2>
                                <form method="POST" action="{{ lroute('petition.claim', ['slug' => $tr->slug ?? '', 'id' => $petition->id]) }}">
                                    @csrf
                                    <div class="mb-3">
                                        <input class="form-control @error('password') is-invalid @enderror" type="password" name="password" minlength="8" placeholder="{{ __('sign.claim_password') }}">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <button class="btn btn-danger" type="submit">{{ __('sign.claim_btn') }}</button>
                                </form>
                                <p class="mt-3 mb-0 text-muted text-[13px]!">
                                    {{ __('sign.claim_verify_note') }}
                                </p>
                            </div>
                        @else
                            <p class="mb-0 text-muted text-sm!">
                                {{ __('sign.claim_existing') }}
                            </p>
                        @endif
                    </div>
                @endif

                <h2 class="mb-3 text-[1.3rem]! md:text-[22px]!">
                    {{ $suggestionsTitle }}
                </h2>

                <div class="mb-4">
                    @forelse($suggestions as $p)
                        @php
                            $sUrl = !empty($p->tr_slug)
                                ? lroute('petition.show', ['slug' => $p->tr_slug, 'id' => $p->id])
                                : '#';
                        @endphp

                        <div class="mb-2">
                            <a class="d-block p-2 border border-[#f0caca]! rounded! bg-[#fff6f6]!"
                                href="{{ $sUrl }}">
                                {{ $p->tr_title ?? __('thanks.petition_fallback') }}
                            </a>
                        </div>
                    @empty
                        <div class="text-muted text-sm!">
                            {{ $suggestionsEmpty }}
                        </div>
                    @endforelse
                </div>

                <a class="btn btn-danger"
                    href="{{ lroute('petition.show', ['slug' => $tr->slug, 'id' => $petition->id]) }}">
                    {{ $inviteBtnText }}
                </a>
            </div>
        </div>
    </section>
@endsection
