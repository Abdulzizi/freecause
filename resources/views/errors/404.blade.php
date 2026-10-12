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
                    <svg xmlns="http://www.w3.org/2000/svg" width="200" height="150" viewBox="0 0 200 150" fill="none" aria-hidden="true">
                        <ellipse cx="100" cy="132" rx="70" ry="8" fill="#f6d4d4"></ellipse>
                        <circle cx="100" cy="66" r="52" fill="#fff5f5"></circle>
                        <circle cx="100" cy="66" r="52" stroke="#f6d4d4" stroke-width="2"></circle>
                        <text x="100" y="84" text-anchor="middle" font-family="sans-serif" font-size="46" font-weight="700" fill="#cc1f1f">404</text>
                        <circle cx="46" cy="34" r="6" fill="#ce2020"></circle>
                        <circle cx="158" cy="40" r="4" fill="#f6a5a5"></circle>
                        <circle cx="150" cy="96" r="5" fill="#ce2020"></circle>
                        <path d="M40 104c6-6 14-6 20 0" stroke="#f6a5a5" stroke-width="3" stroke-linecap="round" fill="none"></path>
                        <path d="M74 48l10 10M84 48l-10 10" stroke="#cc1f1f" stroke-width="3" stroke-linecap="round"></path>
                        <path d="M116 48l10 10M126 48l-10 10" stroke="#cc1f1f" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                </div>
                <div class="fcui-section-title capitalize text-xl md:text-2xl">404</div>

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

                <x-ui.button variant="danger" :href="url('/'.app()->getLocale())" class="mt-6">{{ __('errors.back_home') }}</x-ui.button>
            </div>

        </x-ui.container>
    </section>
@endsection
