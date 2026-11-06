@extends('layouts.legacy')

@section('title', $tr->title)
@section('body_class', $tr->slug)

@if ($fallback)
    @section('canonical', canonical_url($defaultLocale.'/'.$tr->slug))
@endif

@section('content')
    @include('pages.partials.static-layout', [
        'title' => $tr->title,
        'content' => $tr->content,
        'slug' => $tr->slug,
        'fallback' => $fallback
    ])
    @if ($tr->slug === 'privacy-policy' && config('aggregate-traffic.enabled'))
        @php($notice = config('aggregate-traffic.notices.'.app()->getLocale(), config('aggregate-traffic.notices.en')))
        <x-ui.section>
            <h2 class="font-bold mb-3">{{ $notice[0] }}</h2>
            <p>{{ $notice[1] }}</p>
        </x-ui.section>
    @endif
@endsection
