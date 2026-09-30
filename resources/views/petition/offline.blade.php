@extends('layouts.legacy')

@section('title', $tr->title)

@section('content')
    <section class="py-16!">
        <div class="container">
            <div class="bg-white shadow-sm rounded-3 p-5 text-center border border-[#eee]!">
                <h1 class="mb-3">{{ $tr->title }}</h1>
                <p class="mb-4">This petition is currently offline.</p>
                <a class="btn btn-primary" href="{{ lroute('petitions.index') }}">Explore petitions</a>
            </div>
        </div>
    </section>
@endsection
