@extends('layouts.legacy')

@section('title', '429 - Too Many Requests')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="mb-4">
                <h1 class="mb-2" style="font-size:24px; font-weight:600;">Too Many Requests</h1>
                <div style="font-size:14px;">
                    <a class="red" href="{{ url('/'.app()->getLocale()) }}">Home</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">429</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4 mb-5" style="border:1px solid #eee;">
                <div class="mb-2 headings">429</div>

                <p style="font-size:15px; color:#555; margin-bottom:6px;">
                    Error 429: Too many requests.
                </p>
                <p style="font-size:15px; color:#555; margin-bottom:20px;">
                    You have made too many requests in a short period. Please wait a moment and try again.
                </p>
                <p style="font-size:14px; color:#555; margin-bottom:20px;">
                    If you believe this is an error, please contact support.
                </p>
            </div>
        </div>
    </section>
@endsection
