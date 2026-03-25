@php
    $locale = app()->getLocale() ?: 'en';
@endphp

@extends('layouts.legacy')

@section('title', '500 - Server Error')

@section('content')
    <section class="py-5">
        <div class="container">

            <div class="mb-4">
                <h1 class="mb-2" style="font-size:24px; font-weight:600;">Server Error</h1>
                <div style="font-size:14px;">
                    <a class="red" href="{{ url('/'.$locale) }}">Home</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">500</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4 mb-5" style="border:1px solid #eee;">
                <div class="mb-2 headings">500</div>
                <p style="font-size:15px; color:#555; margin-bottom:6px;">
                    Something went wrong on our end.
                </p>
                <p style="font-size:15px; color:#555; margin-bottom:20px;">
                    We are working to fix the issue. Please try again in a few moments.
                </p>
                <a class="btn btn-danger" href="{{ url('/'.$locale) }}">Back to Home</a>
            </div>

        </div>
    </section>
@endsection
