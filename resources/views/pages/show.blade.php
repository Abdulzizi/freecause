@extends('layouts.legacy')

@section('title', $tr->title)

@section('content')
    <h1>{{ $tr->title }}</h1>

    <div>
        {!! $tr->content !!}
    </div>
@endsection
