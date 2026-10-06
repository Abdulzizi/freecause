@php
    $locale = app()->getLocale() ?: 'en';
@endphp

@extends('layouts.legacy')

@section('title', 'Maintenance - FreeCause')

@section('content')
    <section class="py-12!">
        <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">

            <div class="mb-6!">
                <h1 class="mb-2! text-2xl! font-semibold!">{{ __('errors.503_title') }}</h1>
            </div>

            <div class="bg-white! shadow-sm! rounded-lg! p-6! mb-12! border! border-[#eee]!">
                <div class="mb-2! relative! capitalize! text-[1.3rem]! md:text-[1.5rem]! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">503</div>
                <p class="text-[15px]! text-[#555]! mb-1.5!">
                    {{ __('errors.503_lead') }}
                </p>
                <p class="text-[15px]! text-[#555]! mb-5!">
                    {{ __('errors.503_hint') }}
                </p>
                <x-ui.button variant="danger" :href="url('/'.$locale)">{{ __('errors.503_btn') }}</x-ui.button>
            </div>

        </div>
    </section>
@endsection
