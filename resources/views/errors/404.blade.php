@extends('layouts.legacy')

@section('title', '404 - Page not found')

@section('content')
    <section class="py-12!">
        <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">

            @php
                $locale = app()->getLocale();
                $mostRead = \App\Support\ErrorPageData::mostReadPetitions();
            @endphp

            <div class="mb-6!">
                <h1 class="mb-2! text-2xl! font-semibold!">{{ __('errors.404_title') }}</h1>
                <div class="text-sm!">
                    <a class="text-brand!" href="{{ url('/'.app()->getLocale()) }}">{{ __('errors.home') }}</a>
                    <span class="text-[#6c757d]!"> / </span>
                    <span class="text-[#6c757d]!">404</span>
                </div>
            </div>

            <div class="bg-white! shadow-sm! rounded-lg! p-6! mb-12! border! border-[#eee]!">
                <div class="mb-2! relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">404</div>

                <p class="text-[15px]! text-[#555]! mb-1.5!">
                    {{ __('errors.404_lead') }}
                </p>
                <p class="text-[15px]! text-[#555]! mb-5!">
                    {{ __('errors.404_hint') }}
                </p>
                <p class="text-sm! text-[#555]! mb-5!">
                    {{ __('errors.404_suggestions') }}
                </p>

                @if ($mostRead->isNotEmpty())
                        <h2 class="text-lg! font-bold! mb-4!">
                            {{ __('errors.404_most_read') }}
                        </h2>

                        <div class="flex! flex-wrap! -mt-4! -mx-2!">
                            @foreach ($mostRead as $p)
                                <div class="shrink-0! grow-0! mt-4! px-2! w-full! md:w-1/2!">
                                    <div class="p-2.5! border! border-[#f0f0f0]! rounded-md!">
                                        <a class="text-brand! text-sm! font-medium! no-underline!"
                                            href="{{ route('petition.show', [
                                                'locale' => $locale,
                                                'slug' => $p->slug,
                                                'id' => $p->id,
                                            ]) }}">
                                            {{ $p->title }}
                                        </a>
                                        <div class="text-xs! text-[#999]! mt-1!">
                                            {{ number_format($p->signature_count) }} {{ __('errors.signatures') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                @endif
            </div>

        </div>
    </section>
@endsection
