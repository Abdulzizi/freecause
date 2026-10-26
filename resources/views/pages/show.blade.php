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
@endsection
