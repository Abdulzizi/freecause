@php
    $isAdmin = str_starts_with(request()->path(), 'admin');
    $locale = app()->getLocale() ?: 'en';
@endphp

@if ($isAdmin)
    @extends('admin.layouts.app')

    @section('title', 'Forbidden')

    @section('content')
        <div class="fc-box" style="padding:40px; text-align:center; max-width:600px; margin:60px auto;">
            <h1 style="font-size:28px; margin-bottom:15px;">403 - Access Forbidden</h1>
            <p style="margin-bottom:20px; color:#666;">
                You do not have permission to access this section.
            </p>
            <a href="{{ route('admin.options.global') }}" class="fc-btn">
                Go Back to Dashboard
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
                    <h1 class="mb-2" style="font-size:24px; font-weight:600;">Access Forbidden</h1>
                    <div style="font-size:14px;">
                        <a class="red" href="/{{ $locale }}">Home</a>
                        <span class="text-muted"> / </span>
                        <span class="text-muted">403</span>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-3 p-4 mb-5" style="border:1px solid #eee;">
                    <div class="mb-2 headings">403</div>
                    <p style="font-size:15px; color:#555; margin-bottom:6px;">
                        You do not have permission to access this page.
                    </p>
                    <p style="font-size:15px; color:#555; margin-bottom:20px;">
                        If you believe this is a mistake, please contact support.
                    </p>
                    <a class="btn btn-danger" href="/{{ $locale }}">Back to Home</a>
                </div>

            </div>
        </section>
    @endsection
@endif
