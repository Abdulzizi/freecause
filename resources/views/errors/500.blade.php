@php
    $locale = app()->getLocale() ?: 'en';
@endphp

@extends('layouts.legacy')

@section('title', '500 - Server Error')

@section('content')
    <section class="fc-section">
        <x-ui.container>

            <div class="mb-6">
                <h1 class="mb-2 text-2xl font-semibold">{{ __('errors.500_title') }}</h1>
                <div class="text-sm">
                    <a class="text-brand" href="{{ url('/'.$locale) }}">{{ __('errors.home') }}</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">500</span>
                </div>
            </div>

            <div class="bg-white shadow-elev-1 rounded-lg p-6 mb-12 fc-enter">
                <div class="fcui-section-title capitalize text-xl md:text-2xl">500</div>
                <p class="text-sm text-muted mb-1.5">
                    {{ __('errors.500_lead') }}
                </p>
                <p class="text-sm text-muted mb-5">
                    {{ __('errors.500_hint') }}
                </p>
                <x-ui.button variant="danger" :href="url('/'.$locale)" class="hidden max-md:min-h-12 md:inline-flex">{{ __('errors.back_home') }}</x-ui.button>
            </div>

            <x-ui.sticky-action :href="url('/'.$locale)" :label="__('errors.back_home')" />

        </x-ui.container>

        <x-ads.rotate placement="inline" class="mt-6 text-center" />
        <x-ads.rotate placement="page_mid" class="mt-6" />
    </section>
@endsection
