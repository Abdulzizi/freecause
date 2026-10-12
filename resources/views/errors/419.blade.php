@extends('layouts.legacy')

@section('title', '419 - Session Expired')

@section('content')
    <section class="fc-section">
        <x-ui.container>
            <div class="mb-6">
                <h1 class="mb-2 text-2xl font-semibold">{{ __('errors.419_title') }}</h1>
                <div class="text-sm">
                    <a class="text-brand" href="{{ url('/'.app()->getLocale()) }}">{{ __('errors.home') }}</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">419</span>
                </div>
            </div>

            <div class="bg-white shadow-elev-1 rounded-lg p-6 mb-12 fc-enter">
                <div class="fcui-section-title capitalize text-xl md:text-2xl">419</div>

                <p class="text-sm text-muted mb-1.5">
                    {{ __('errors.419_lead') }}
                </p>
                <p class="text-sm text-muted mb-5">
                    {{ __('errors.419_hint') }}
                </p>
                <p class="text-sm text-muted mb-5">
                    <a href="{{ url()->previous() }}" class="text-brand">{{ __('errors.419_back') }}</a>
                </p>
            </div>
        </x-ui.container>
    </section>
@endsection
