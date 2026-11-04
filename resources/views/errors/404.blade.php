@extends('layouts.legacy')

@section('title', __('errors.404_title') . ' | FreeCause')

@section('content')
    @php
        $locale = app()->getLocale();
        $mostRead = \App\Support\ErrorPageData::mostReadPetitions();
    @endphp

    <section id="fc-404-recovery" class="fc-section">
        <x-ui.container>
            <div class="bg-white border border-border rounded-lg shadow-elev-1 overflow-hidden mb-8">
                <div class="grid md:grid-cols-2 gap-6 items-center p-6 md:p-8">
                    <div class="flex justify-center bg-brand-soft rounded-lg p-6" aria-hidden="true">
                        <svg width="360" height="240" viewBox="0 0 360 240" fill="none" class="max-w-full h-auto" focusable="false">
                            <circle cx="180" cy="120" r="108" fill="var(--fc-surface)" />
                            <path d="M92 48h122l42 42v104H92V48Z" fill="var(--fc-surface)" stroke="var(--fc-border-strong)" stroke-width="3" stroke-linejoin="round" />
                            <path d="M214 48v42h42" stroke="var(--fc-border-strong)" stroke-width="3" stroke-linejoin="round" />
                            <text x="174" y="148" text-anchor="middle" fill="var(--fc-brand)" font-family="system-ui, sans-serif" font-size="64" font-weight="800">404</text>
                            <path d="M120 171h62" stroke="var(--fc-border-strong)" stroke-width="5" stroke-linecap="round" />
                            <circle cx="252" cy="170" r="34" fill="var(--fc-brand-soft)" stroke="var(--fc-brand)" stroke-width="6" />
                            <path d="m277 195 28 28" stroke="var(--fc-brand)" stroke-width="10" stroke-linecap="round" />
                            <path d="M236 157a22 22 0 0 1 17-9" stroke="var(--fc-surface)" stroke-width="4" stroke-linecap="round" />
                            <path d="m64 80 8-8m-8 0 8 8m216-32 10 10m-10 0 10-10" stroke="var(--fc-brand)" stroke-width="3" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="space-y-4">
                        <span class="inline-flex bg-brand-soft text-brand font-bold px-3 py-1 rounded-sm">404</span>
                        <h1 class="text-3xl md:text-4xl font-bold text-ink">{{ __('errors.404_title') }}</h1>
                        <p class="text-muted">{{ __('errors.404_hint') }}</p>
                        <p class="text-sm text-muted">{{ __('errors.404_suggestions') }}</p>
                        <div class="flex flex-wrap gap-3 pt-2">
                            <x-ui.button :href="url('/'.$locale)">{{ __('errors.back_home') }}</x-ui.button>
                            <x-ui.button variant="secondary" :href="lroute('petitions.index')">{{ __('list.petitions') }}</x-ui.button>
                        </div>
                    </div>
                </div>
            </div>

            <x-ads.slot name="rectangle" class="my-8 text-center" />

            @if ($mostRead->isNotEmpty())
                <section class="mb-8" aria-labelledby="fc-404-petitions">
                    <h2 id="fc-404-petitions" class="text-xl font-bold mb-4">{{ __('errors.404_most_read') }}</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach ($mostRead as $p)
                            <a class="block bg-white border border-border rounded-lg p-5 text-brand" data-prefetch
                               href="{{ route('petition.show', ['locale' => $locale, 'slug' => $p->slug, 'id' => $p->id]) }}">
                                <h3 class="font-semibold mb-2 break-words">{{ $p->title }}</h3>
                                <span class="text-sm text-muted">{{ number_format($p->signature_count) }} {{ __('errors.signatures') }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <x-ads.rotate placement="page_mid" class="my-8 text-center" />
        </x-ui.container>
    </section>
@endsection
