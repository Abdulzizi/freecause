@extends('layouts.legacy')

@section('title', __('nav.magazine').' - FreeCause')
@section('body_class', 'magazine')

@section('content')
    <section class="bg-[var(--fc-band)]! fc-section">
        <x-ui.container>
            <div class="flex! flex-wrap! -mx-3!">
                <div class="w-full! shrink-0! grow-0! px-3!">
                    <h1 class="text-xl! mb-[5px]!">{{ __('nav.magazine') }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0! p-0!">
                            <li class="breadcrumb-item">
                                <a href="{{ lroute('home') }}">{{ __('nav.home') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('nav.magazine_breadcrumb') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </x-ui.container>
    </section>

    <section class="py-14! md:py-24!">
        <x-ui.container>
            <div class="flex! flex-wrap! -mx-3! justify-center!">
                <div class="w-full! md:w-2/3! shrink-0! grow-0! px-3! py-4!">
                    <x-ui.alert variant="warning" class="mb-4">
                        <strong>{{ __('nav.magazine_notice_title') }}</strong>
                        {{ __('nav.magazine_notice_body') }}
                    </x-ui.alert>
                    <div class="text-center! py-6!">
                        <h2 class="mb-4! font-semibold!">{{ __('nav.magazine_coming_soon') }}</h2>
                        <p class="text-muted! text-base!">
                            {{ __('nav.magazine_body') }}
                        </p>
                        <x-ui.button :href="lroute('home')" variant="danger" class="mt-3">{{ __('nav.magazine_back_home') }}</x-ui.button>
                    </div>
                </div>
            </div>
        </x-ui.container>
    </section>
@endsection
