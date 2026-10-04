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
            <p class="mb-5! text-[#666]!">
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
        <section class="py-5">
            <div class="container">

                <div class="mb-4">
                    <h1 class="mb-2 text-2xl! font-semibold!">{{ __('errors.403_title') }}</h1>
                    <div class="text-sm!">
                        <a class="text-brand!" href="{{ url('/'.$locale) }}">{{ __('errors.home') }}</a>
                        <span class="text-muted"> / </span>
                        <span class="text-muted">403</span>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-3 p-4 mb-5 border! border-[#eee]!">
                    <div class="mb-2 relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">403</div>
                    <p class="text-[15px]! text-[#555]! mb-1.5!">
                        {{ __('errors.403_lead') }}
                    </p>
                    <p class="text-[15px]! text-[#555]! mb-5!">
                        {{ __('errors.403_hint') }}
                    </p>
                    <a class="btn btn-danger" href="{{ url('/'.$locale) }}">{{ __('errors.back_home') }}</a>
                </div>

            </div>
        </section>
    @endsection
@endif
