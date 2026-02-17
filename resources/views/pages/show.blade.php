@extends('layouts.legacy')

@section('title', $tr->title)
@section('body_class', $tr->slug)

@section('content')
    @include('pages.partials.static-layout', [
        'title' => $tr->title,
        'content' => $tr->content
    ])
@endsection
