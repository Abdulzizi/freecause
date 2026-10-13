@php
    $isAdmin = str_starts_with(request()->path(), 'admin');
    $locale = app()->getLocale() ?: 'en';
@endphp

@if ($isAdmin)
    @extends('admin.layouts.app')

    @section('title', 'Forbidden')

    @section('content')
        <div class="fc-box p-10 text-center max-w-xl my-16 mx-auto">
            <h1 class="text-2xl mb-4">403 - {{ __('errors.403_admin_title') }}</h1>
            <p class="mb-5 text-muted">
                {{ __('errors.403_admin_lead') }}
            </p>
            <a href="{{ route('admin.options.global') }}" class="fc-btn">
                {{ __('errors.403_admin_btn') }}
            </a>
        </div>
    @endsection
@else
    @extends('layouts.legacy')

    @section('title', '403 - Forbidden')

    @section('content')
        <section class="fc-section">
            <x-ui.container>

                <div class="mb-6">
                    <h1 class="mb-2 text-2xl font-semibold">{{ __('errors.403_title') }}</h1>
                    <div class="text-sm">
                        <a class="text-brand" href="{{ url('/'.$locale) }}">{{ __('errors.home') }}</a>
                        <span class="text-muted"> / </span>
                        <span class="text-muted">403</span>
                    </div>
                </div>

                <div class="bg-white shadow-elev-1 rounded-lg p-6 mb-12 fc-enter">
                    <div class="fcui-section-title capitalize text-xl md:text-2xl">403</div>
                    <p class="text-sm text-muted mb-1.5">
                        {{ __('errors.403_lead') }}
                    </p>
                    <p class="text-sm text-muted mb-5">
                        {{ __('errors.403_hint') }}
                    </p>
                    <x-ui.button variant="danger" :href="url('/'.$locale)" class="hidden max-md:min-h-12 md:inline-flex">{{ __('errors.back_home') }}</x-ui.button>
                </div>

                <x-ui.sticky-action :href="url('/'.$locale)" :label="__('errors.back_home')" />

            </x-ui.container>
        </section>
    @endsection
@endif
