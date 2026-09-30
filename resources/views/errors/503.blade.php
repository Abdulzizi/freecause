@php
    $locale = 'en';
@endphp

@extends('layouts.legacy')

@section('title', 'Maintenance - FreeCause')

@section('content')
    <section class="py-5">
        <div class="container">

            <div class="mb-4">
                <h1 class="mb-2 text-2xl! font-semibold!">Under Maintenance</h1>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4 mb-5 border! border-[#eee]!">
                <div class="mb-2 relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">503</div>
                <p class="text-[15px]! text-[#555]! mb-1.5!">
                    FreeCause is currently undergoing scheduled maintenance.
                </p>
                <p class="text-[15px]! text-[#555]! mb-5!">
                    We will be back shortly. Thank you for your patience.
                </p>
                <a class="btn btn-danger" href="/{{ $locale }}">Try Again</a>
            </div>

        </div>
    </section>
@endsection
