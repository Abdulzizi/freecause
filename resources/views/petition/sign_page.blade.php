@extends('layouts.legacy')

@php
    $pageTitle = __('sign.page_title', ['title' => $tr->title ?? __('common.petition')]);

    $h2Line1 = __('sign.h2_line1');
    $h2Line2 = __('sign.h2_line2');
    $btnText = __('sign.btn');

    $signUrl = isset($tr) && $tr ? lroute('petition.sign', ['slug' => $tr->slug, 'id' => $petition->id]) : '#';

    $signData = session('sign.'.$petition->id, []);
@endphp

@section('title', $pageTitle)

@push('head')
    <style>@view-transition { navigation: none; }</style>
@endpush

@section('content')
    <div class="mb-4 text-center hidden lg:block">
        <x-ads.slot name="leaderboard" />
    </div>
    <x-ui.section>
            <div class="bg-white shadow-elev-1 rounded-md p-6 md:p-12 text-center max-w-[900px] mx-auto">

                <h1 class="mb-6 text-xl md:text-2xl font-medium break-words [overflow-wrap:anywhere]">
                    {{ $h2Line1 }}<br>
                    {{ $h2Line2 }}
                </h1>

                <div class="mb-4 text-center lg:hidden">
                    <x-ads.slot name="mobile" />
                </div>

                <x-ads.slot name="mobile_mini" class="lg:hidden text-center mb-4" />
                <x-ads.slot name="mobile_inline" class="lg:hidden text-center mb-4" />
                <x-ads.slot name="mobile_native_small" class="lg:hidden text-center mb-4" />

                @if (auth()->check() || ! empty($signData))
                <form action="{{ $signUrl }}" method="POST" data-sign-busy class="inline-block w-full md:w-auto">
                    @csrf
                    <x-ui.alert variant="error" class="mt-2" data-sign-network-error hidden>{{ __('sign.connection_error') }}</x-ui.alert>

                    @if ($errors->any())
                        <x-ui.alert variant="error" class="text-start mt-2">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </x-ui.alert>
                    @endif

                    <input type="hidden" name="agree1" value="{{ data_get($signData, 'agree1', 'agree') }}">
                    <input type="hidden" name="agree2" value="{{ data_get($signData, 'agree2', 'agree') }}">
                    <input type="hidden" name="agree3" value="{{ data_get($signData, 'agree3', 'agree') }}">

                    <input type="hidden" name="comment" value="{{ data_get($signData, 'comment', __('sign.default_comment')) }}">
                    <button class="inline-flex items-center justify-center gap-2 bg-brand text-white border border-brand rounded-lg px-5 py-3 text-xl font-semibold no-underline hover:bg-brand-hover max-md:hidden" type="submit">
                        {{ $btnText }} <span>»</span>
                    </button>

                    <x-ui.sticky-action :label="$btnText" />
                </form>
                @else
                <div class="text-start">
                    @include('petition.partials._sign_form', [
                        'variant' => 'stack',
                        'petition' => $petition,
                        'tr' => $tr,
                        'locale' => $locale,
                        'hasSigned' => false,
                        'stickySubmit' => true,
                    ])
                </div>
                @endif
            </div>
        </x-ui.section>

    <div class="mb-4 text-center">
        <x-ads.slot name="rectangle" />
    </div>
    <x-ads.slot name="mobile_strip" sticky class="lg:hidden text-center sticky bottom-2 z-sticky" />
@endsection
