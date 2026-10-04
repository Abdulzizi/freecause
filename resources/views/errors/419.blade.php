@extends('layouts.legacy')

@section('title', '419 - Session Expired')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="mb-4">
                <h1 class="mb-2 text-2xl! font-semibold!">{{ __('errors.419_title') }}</h1>
                <div class="text-sm!">
                    <a class="text-brand!" href="{{ url('/'.app()->getLocale()) }}">{{ __('errors.home') }}</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">419</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4 mb-5 border! border-[#eee]!">
                <div class="mb-2 relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">419</div>

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
