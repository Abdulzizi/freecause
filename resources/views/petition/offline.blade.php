@extends('layouts.legacy')

@section('title', $tr->title)

@section('content')
    <section class="py-16!">
        <div class="container">
            <div class="bg-white shadow-sm rounded-3 p-5 text-center border border-[#eee]!">
                <h1 class="mb-3">{{ $tr->title }}</h1>
                <p class="mb-4">{{ __('pet.offline_title') }}</p>
                <a class="btn btn-primary" href="{{ lroute('petitions.index') }}">{{ __('pet.explore') }}</a>
            </div>
        </div>
    </section>
@endsection
