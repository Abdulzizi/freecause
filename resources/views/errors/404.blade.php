@extends('layouts.legacy')

@section('title', '404 - Page not found')

@section('content')
    <section class="py-5">
        <div class="container">

            <div class="mb-4">
                <h1 class="mb-2" style="font-size:24px;font-weight:600;">Page not found</h1>
                <div style="font-size:14px;">
                    <a class="red" href="/{{ app()->getLocale() }}">Home</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">404</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4" style="border:1px solid #eee;">
                <div class="mb-2" style="font-weight:700;">404</div>
                <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                    <div style="height:2px;width:72%;background:#cc1f1f;position:absolute;left:0;top:0;"></div>
                </div>

                <h2 style="font-size:20px;font-weight:700;margin-bottom:10px;">
                    Oops, we can’t find that page.
                </h2>

                <p class="text-muted" style="max-width:720px;line-height:1.7;">
                    The page you’re looking for may have been moved, deleted, or the URL could be incorrect.
                    Please check the address or go back to the homepage.
                </p>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a class="btn btn-primary" href="/{{ app()->getLocale() }}">Back to Home</a>
                    <a class="btn btn-outline-secondary" href="/{{ app()->getLocale() }}/faqs">Help / FAQs</a>
                </div>
            </div>

        </div>
    </section>
@endsection
