@extends('layouts.legacy')

@section('title', '429 - Too Many Requests')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="mb-4">
                <h1 class="mb-2 text-2xl! font-semibold!">Too Many Requests</h1>
                <div class="text-sm!">
                    <a class="text-brand!" href="{{ url('/'.app()->getLocale()) }}">Home</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">429</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4 mb-5 border! border-[#eee]!">
                <div class="mb-2 relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">429</div>

                <p class="text-[15px]! text-[#555]! mb-1.5!">
                    Error 429: Too many requests.
                </p>
                <p class="text-[15px]! text-[#555]! mb-5!">
                    You have made too many requests in a short period. Please wait a moment and try again.
                </p>
                <p class="text-sm! text-[#555]! mb-5!">
                    If you believe this is an error, please contact support.
                </p>
            </div>
        </div>
    </section>
@endsection
