@extends('layouts.legacy')

@section('title', __('nav.magazine').' - FreeCause')
@section('body_class', 'magazine')

@section('content')
    {{-- ad: leaderboard, desktop only --}}
    <div class="mb-4 text-center hidden lg:block">
        <x-ads.slot name="leaderboard" />
    </div>
    {{-- ad: mobile banner, mobile only --}}
    <div class="mb-4 text-center lg:hidden">
        <x-ads.slot name="mobile" />
    </div>

    <x-ui.page-head :band="true" :title="__('nav.magazine')" :breadcrumb="[
        ['label' => __('nav.home'), 'url' => lroute('home')],
        ['label' => __('nav.magazine_breadcrumb')],
    ]" />

    <x-ui.section>
        <div class="flex flex-wrap -mx-3 justify-center">
                <div class="w-full md:w-2/3 shrink-0 grow-0 px-3 py-4">
                    <x-ui.alert variant="warning" class="mb-4">
                        <strong>{{ __('nav.magazine_notice_title') }}</strong>
                        {{ __('nav.magazine_notice_body') }}
                    </x-ui.alert>
                    <div class="text-center py-6">
                        <h2 class="mb-4 font-semibold">{{ __('nav.magazine_coming_soon') }}</h2>
                        <p class="text-muted text-base">
                            {{ __('nav.magazine_body') }}
                        </p>
                        <x-ui.button :href="lroute('home')" variant="danger" class="mt-3">{{ __('nav.magazine_back_home') }}</x-ui.button>
                    </div>
                </div>
            </div>
    </x-ui.section>
@endsection
