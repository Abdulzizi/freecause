@extends('layouts.legacy')

@section('title', $tr->title)

@section('content')
    <section class="py-14 md:py-24">
        <x-ui.container>
            <div class="bg-white shadow-sm rounded-[.5rem] p-12 text-center border border-border">
                <h1 class="mb-4">{{ $tr->title }}</h1>
                <p class="mb-6">{{ __('pet.offline_title') }}</p>
                <x-ui.button variant="primary" :href="lroute('petitions.index')">{{ __('pet.explore') }}</x-ui.button>
            </div>
        </x-ui.container>
    </section>
@endsection
