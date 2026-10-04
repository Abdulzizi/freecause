@extends('layouts.legacy')

@section('title', $tr->title)

@section('content')
    <section class="py-16!">
        <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">
            <div class="bg-white! shadow-sm! rounded-[.5rem]! p-12! text-center! border! border-[#eee]!">
                <h1 class="mb-4!">{{ $tr->title }}</h1>
                <p class="mb-6!">{{ __('pet.offline_title') }}</p>
                <x-ui.button variant="primary" :href="lroute('petitions.index')">{{ __('pet.explore') }}</x-ui.button>
            </div>
        </div>
    </section>
@endsection
