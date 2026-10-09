@php
    $isAdmin = str_starts_with(request()->path(), 'admin');
    $locale = app()->getLocale() ?: 'en';
@endphp

@if ($isAdmin)
    @extends('admin.layouts.app')

    @section('title', 'Forbidden')

    @section('content')
        <div class="fc-box p-10! text-center! max-w-[600px]! my-[60px]! mx-auto!">
            <h1 class="text-[28px]! mb-4!">403 - {{ __('errors.403_admin_title') }}</h1>
            <p class="mb-5! text-muted!">
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
        <section class="py-12!">
            <x-ui.container>

                <div class="mb-6!">
                    <h1 class="mb-2! text-2xl! font-semibold!">{{ __('errors.403_title') }}</h1>
                    <div class="text-sm!">
                        <a class="text-brand!" href="{{ url('/'.$locale) }}">{{ __('errors.home') }}</a>
                        <span class="text-muted!"> / </span>
                        <span class="text-muted!">403</span>
                    </div>
                </div>

                <div class="bg-white! shadow-sm! rounded-lg! p-6! mb-12! border! border-border!">
                    <div class="fcui-section-title capitalize! text-[1.3rem]! md:text-[1.5rem]!">403</div>
                    <p class="text-[15px]! text-muted! mb-1.5!">
                        {{ __('errors.403_lead') }}
                    </p>
                    <p class="text-[15px]! text-muted! mb-5!">
                        {{ __('errors.403_hint') }}
                    </p>
                    <x-ui.button variant="danger" :href="url('/'.$locale)">{{ __('errors.back_home') }}</x-ui.button>
                </div>

            </x-ui.container>
        </section>
    @endsection
@endif
