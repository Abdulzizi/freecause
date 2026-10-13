@extends('layouts.legacy')

@section('title', '404 - Page not found')

@section('content')
    <section class="fc-section">
        <x-ui.container>

            @php
                $locale = app()->getLocale();
                $mostRead = \App\Support\ErrorPageData::mostReadPetitions();
            @endphp

            <div class="mb-6">
                <h1 class="mb-2 text-2xl font-semibold">{{ __('errors.404_title') }}</h1>
                <div class="text-sm">
                    <a class="text-brand" href="{{ url('/'.app()->getLocale()) }}">{{ __('errors.home') }}</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">404</span>
                </div>
            </div>

            <div class="bg-white shadow-elev-1 rounded-lg p-6 mb-12 fc-enter">
                <div class="flex justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="220" height="156" viewBox="0 0 240 170" fill="none" aria-hidden="true">
                        <rect x="52" y="16" width="120" height="138" rx="10" fill="var(--fc-surface)" stroke="var(--fc-border-strong)" stroke-width="2"></rect>
                        <path d="M144 16h18a10 10 0 0 1 10 10v10h-18a10 10 0 0 1-10-10z" fill="var(--fc-surface-2)" stroke="var(--fc-border-strong)" stroke-width="2" stroke-linejoin="round"></path>
                        <line x1="74" y1="78" x2="150" y2="78" stroke="var(--fc-border)" stroke-width="4" stroke-linecap="round"></line>
                        <line x1="74" y1="98" x2="134" y2="98" stroke="var(--fc-border)" stroke-width="4" stroke-linecap="round"></line>
                        <line x1="74" y1="118" x2="142" y2="118" stroke="var(--fc-border)" stroke-width="4" stroke-linecap="round"></line>
                        <line x1="74" y1="138" x2="112" y2="138" stroke="var(--fc-border)" stroke-width="4" stroke-linecap="round"></line>
                        <circle cx="176" cy="112" r="32" fill="var(--fc-brand-soft)" stroke="var(--fc-brand)" stroke-width="4"></circle>
                        <path d="M158 96a24 24 0 0 1 18-14" stroke="var(--fc-surface)" stroke-width="4" stroke-linecap="round"></path>
                        <line x1="199" y1="135" x2="224" y2="160" stroke="var(--fc-brand)" stroke-width="8" stroke-linecap="round"></line>
                    </svg>
                </div>

                <p class="text-sm text-muted mb-1.5">
                    {{ __('errors.404_lead') }}
                </p>
                <p class="text-sm text-muted mb-5">
                    {{ __('errors.404_hint') }}
                </p>
                <p class="text-sm text-muted mb-5">
                    {{ __('errors.404_suggestions') }}
                </p>

                @if ($mostRead->isNotEmpty())
                        <h2 class="text-lg font-bold mb-4">
                            {{ __('errors.404_most_read') }}
                        </h2>

                        <div class="flex flex-wrap -mt-4 -mx-2">
                            @foreach ($mostRead as $p)
                                <div class="shrink-0 grow-0 mt-4 px-2 w-full md:w-1/2">
                                    <div class="p-2.5 border border-border rounded-md">
                                        <a class="text-brand text-sm font-medium no-underline" data-prefetch
                                            href="{{ route('petition.show', [
                                                'locale' => $locale,
                                                'slug' => $p->slug,
                                                'id' => $p->id,
                                            ]) }}">
                                            {{ $p->title }}
                                        </a>
                                        <div class="text-xs text-muted mt-1">
                                            {{ number_format($p->signature_count) }} {{ __('errors.signatures') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                @endif

                <x-ui.button variant="danger" :href="url('/'.app()->getLocale())" class="mt-6 hidden min-h-12 md:inline-flex">{{ __('errors.back_home') }}</x-ui.button>
            </div>

            <x-ui.sticky-action :href="url('/'.app()->getLocale())" :label="__('errors.back_home')" />

        </x-ui.container>
    </section>
@endsection
