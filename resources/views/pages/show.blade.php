@extends('layouts.legacy')

@section('title', $tr->title)
@section('body_class', $tr->slug)

@if ($fallback)
    @push('head')
        <link rel="canonical" href="{{ url($defaultLocale.'/'.$tr->slug) }}">
    @endpush
@endif

@section('content')
    @include('pages.partials.static-layout', [
        'title' => $tr->title,
        'content' => $tr->content,
        'slug' => $tr->slug,
        'fallback' => $fallback
    ])
@endsection
