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
    <x-ui.section>
            <div class="bg-white shadow-elev-1 rounded-md p-12 text-center max-w-[900px] mx-auto">

                <h1 class="mb-6 text-xl md:text-2xl font-medium break-words [overflow-wrap:anywhere]">
                    {{ $h2Line1 }}<br>
                    {{ $h2Line2 }}
                </h1>

                @auth
                <form action="{{ $signUrl }}" method="POST" data-sign-busy class="inline-block">
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

                    <input type="hidden" name="agree1" value="{{ data_get($signData, 'agree1', '1') }}">
                    <input type="hidden" name="agree2" value="{{ data_get($signData, 'agree2', '1') }}">
                    <input type="hidden" name="agree3" value="{{ data_get($signData, 'agree3', '1') }}">

                    <input type="hidden" name="comment" value="{{ data_get($signData, 'comment', __('sign.default_comment')) }}">
                    <button class="inline-flex items-center justify-center gap-2 bg-brand text-white border border-brand rounded-lg px-5 py-3 text-xl font-semibold no-underline hover:bg-brand-hover max-md:w-full max-md:text-lg max-md:px-4 max-md:py-2.5" type="submit">
                        {{ $btnText }} <span>»</span>
                    </button>
                </form>
                @else
                <div class="text-start">
                    @include('petition.partials._sign_form', [
                        'variant' => 'stack',
                        'petition' => $petition,
                        'tr' => $tr,
                        'locale' => $locale,
                        'hasSigned' => false,
                    ])
                </div>
                @endauth
            </div>
        </x-ui.section>
@endsection
