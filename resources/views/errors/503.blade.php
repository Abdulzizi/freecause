@php
    $locale = app()->getLocale() ?: 'en';
@endphp

@extends('layouts.legacy')

@section('title', 'Maintenance - FreeCause')

@section('content')
    <section class="fc-section">
        <x-ui.container>

            <div class="mb-6">
                <h1 class="mb-2 text-2xl font-semibold">{{ __('errors.503_title') }}</h1>
            </div>

            <div class="bg-white shadow-elev-1 rounded-lg p-6 mb-12 fc-enter">
                <div class="fcui-section-title capitalize text-xl md:text-2xl">503</div>
                <p class="text-sm text-muted mb-1.5">
                    {{ __('errors.503_lead') }}
                </p>
                <p class="text-sm text-muted mb-5">
                    {{ __('errors.503_hint') }}
                </p>
                <x-ui.button variant="danger" :href="url('/'.$locale)" class="hidden max-md:min-h-12 md:inline-flex">{{ __('errors.503_btn') }}</x-ui.button>
            </div>

            <x-ui.sticky-action :href="url('/'.$locale)" :label="__('errors.503_btn')" />

        </x-ui.container>

        <x-ads.rotate placement="inline" class="mt-6 text-center" />
        <x-ads.rotate placement="page_mid" class="mt-6" />
    </section>
@endsection
