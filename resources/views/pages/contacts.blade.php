@extends('layouts.legacy')

@section('title', __('contacts.title'))
@section('body_class', 'contacts')

@section('content')
    <section class="bg-[#fbfbfb]! py-10! md:py-16!">
        <x-ui.container>
            <div class="flex! flex-wrap! -mx-3!">
                <div class="w-full! shrink-0! grow-0! px-3!">

                    <h1 class="text-2xl! md:text-3xl! font-semibold! mb-1!">{{ __('contacts.title') }}</h1>

                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0! p-0!">
                            <li class="breadcrumb-item">
                                <a href="{{ lroute('home') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('contacts.title') }}
                            </li>
                        </ol>
                    </nav>

                </div>
            </div>
        </x-ui.container>
    </section>

    <section class="petitions-list py-14! md:py-24!">
        <x-ui.container>
            <div class="flex! flex-wrap! -mx-3!">
                <div class="w-full! shrink-0! grow-0! px-3!">

                    <div class="fcui-card relative! flex! flex-col! min-w-0! w-full! max-w-[720px]! mx-auto!">
                        <div class="grow! shrink! basis-auto! p-6!">

                            <h2 class="fcui-section-title capitalize! text-[18px]!">{{ __('contacts.title') }}</h2>

                            <div class="mb-6!">
                                {!! __('contacts.intro') !!}
                            </div>

                            @include('pages.partials.contacts-form')

                        </div>
                    </div>

                </div>
            </div>
        </x-ui.container>
    </section>
@endsection