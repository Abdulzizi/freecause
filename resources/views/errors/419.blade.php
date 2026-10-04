@extends('layouts.legacy')

@section('title', '419 - Session Expired')

@section('content')
    <section class="py-12!">
        <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">
            <div class="mb-6!">
                <h1 class="mb-2! text-2xl! font-semibold!">{{ __('errors.419_title') }}</h1>
                <div class="text-sm!">
                    <a class="text-brand!" href="{{ url('/'.app()->getLocale()) }}">{{ __('errors.home') }}</a>
                    <span class="text-[#6c757d]!"> / </span>
                    <span class="text-[#6c757d]!">419</span>
                </div>
            </div>

            <div class="bg-white! shadow-sm! rounded-lg! p-6! mb-12! border! border-[#eee]!">
                <div class="mb-2! relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">419</div>

                <p class="text-[15px]! text-[#555]! mb-1.5!">
                    {{ __('errors.419_lead') }}
                </p>
                <p class="text-[15px]! text-[#555]! mb-5!">
                    {{ __('errors.419_hint') }}
                </p>
                <p class="text-sm! text-[#555]! mb-5!">
                    <a href="{{ url()->previous() }}" class="text-brand!">{{ __('errors.419_back') }}</a>
                </p>
            </div>
        </div>
    </section>
@endsection
