@extends('layouts.legacy')

@section('title', '429 - Too Many Requests')

@section('content')
    <section class="fc-section">
        <x-ui.container>
            <div class="mb-6">
                <h1 class="mb-2 text-2xl font-semibold">{{ __('errors.429_title') }}</h1>
                <div class="text-sm">
                    <a class="text-brand" href="{{ url('/'.app()->getLocale()) }}">{{ __('errors.home') }}</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">429</span>
                </div>
            </div>

            <div class="bg-white shadow-elev-1 rounded-lg p-6 mb-12 fc-enter">
                <div class="fcui-section-title capitalize text-xl md:text-2xl">429</div>

                <p class="text-sm text-muted mb-1.5">
                    {{ __('errors.429_lead') }}
                </p>
                <p class="text-sm text-muted mb-5">
                    {{ __('errors.429_hint') }}
                </p>
                <p class="text-sm text-muted mb-5">
                    {{ __('errors.429_support') }}
                </p>
                <x-ui.button variant="danger" :href="url('/'.app()->getLocale())" class="hidden min-h-12 md:inline-flex">{{ __('errors.back_home') }}</x-ui.button>
            </div>

            <x-ui.sticky-action :href="url('/'.app()->getLocale())" :label="__('errors.back_home')" />
        </x-ui.container>
    </section>
@endsection
