@php
    $locale = app()->getLocale() ?: 'en';
@endphp

@extends('layouts.legacy')

@section('title', 'Maintenance - FreeCause')

@section('content')
    <section class="py-12!">
        <x-ui.container>

            <div class="mb-6!">
                <h1 class="mb-2! text-2xl! font-semibold!">{{ __('errors.503_title') }}</h1>
            </div>

            <div class="bg-white! shadow-sm! rounded-lg! p-6! mb-12! border! border-border!">
                <div class="fcui-section-title capitalize! text-[1.3rem]! md:text-[1.5rem]!">503</div>
                <p class="text-[15px]! text-muted! mb-1.5!">
                    {{ __('errors.503_lead') }}
                </p>
                <p class="text-[15px]! text-muted! mb-5!">
                    {{ __('errors.503_hint') }}
                </p>
                <x-ui.button variant="danger" :href="url('/'.$locale)">{{ __('errors.503_btn') }}</x-ui.button>
            </div>

        </x-ui.container>
    </section>
@endsection
