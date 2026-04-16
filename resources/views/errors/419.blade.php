@extends('layouts.legacy')

@section('title', '419 - Session Expired')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="mb-4">
                <h1 class="mb-2" style="font-size:24px; font-weight:600;">Session Expired</h1>
                <div style="font-size:14px;">
                    <a class="red" href="{{ url('/'.app()->getLocale()) }}">Home</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">419</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4 mb-5" style="border:1px solid #eee;">
                <div class="mb-2 headings">419</div>

                <p style="font-size:15px; color:#555; margin-bottom:6px;">
                    Error 419: Page expired.
                </p>
                <p style="font-size:15px; color:#555; margin-bottom:20px;">
                    Your session has expired. Please refresh the page and try again.
                </p>
                <p style="font-size:14px; color:#555; margin-bottom:20px;">
                    <a href="{{ url()->previous() }}" class="red">Click here</a> to go back to the previous page.
                </p>
            </div>
        </div>
    </section>
@endsection
