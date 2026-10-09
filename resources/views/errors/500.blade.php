@php
    $locale = app()->getLocale() ?: 'en';
@endphp

@extends('layouts.legacy')

@section('title', '500 - Server Error')

@section('content')
    <section class="py-12!">
        <x-ui.container>

            <div class="mb-6!">
                <h1 class="mb-2! text-2xl! font-semibold!">{{ __('errors.500_title') }}</h1>
                <div class="text-sm!">
                    <a class="text-brand!" href="{{ url('/'.$locale) }}">{{ __('errors.home') }}</a>
                    <span class="text-muted!"> / </span>
                    <span class="text-muted!">500</span>
                </div>
            </div>

            <div class="bg-white! shadow-sm! rounded-lg! p-6! mb-12! border! border-border!">
                <div class="fcui-section-title capitalize! text-[1.3rem]! md:text-[1.5rem]!">500</div>
                <p class="text-[15px]! text-muted! mb-1.5!">
                    {{ __('errors.500_lead') }}
                </p>
                <p class="text-[15px]! text-muted! mb-5!">
                    {{ __('errors.500_hint') }}
                </p>
                <x-ui.button variant="danger" :href="url('/'.$locale)">{{ __('errors.back_home') }}</x-ui.button>
            </div>

        </x-ui.container>
    </section>
@endsection
