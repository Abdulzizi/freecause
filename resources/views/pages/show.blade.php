@extends('layouts.legacy')

@section('title', $tr->title)
@section('body_class', $tr->slug)

@if ($fallback)
    @push('head')
        <link rel="canonical" href="{{ url($defaultLocale.'/'.$tr->slug) }}">
    @endpush
@endif

@section('content')
    {{-- ad: leaderboard, desktop only --}}
    <div class="mb-4 text-center hidden lg:block">
        <x-ads.slot name="leaderboard" />
    </div>
    {{-- ad: mobile banner, mobile only --}}
    <div class="mb-4 text-center lg:hidden">
        <x-ads.slot name="mobile" />
    </div>

    @include('pages.partials.static-layout', [
        'title' => $tr->title,
        'content' => $tr->content,
        'slug' => $tr->slug,
        'fallback' => $fallback
    ])
@endsection
