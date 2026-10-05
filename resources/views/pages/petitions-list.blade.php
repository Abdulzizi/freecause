@extends('layouts.legacy')

@section('title', ($pageTitle ?? __('list.petitions')) . ' - FreeCause')

@section('content')
    <section class="py-16!">
        <div class="mx-auto! w-full! px-3! max-w-[540px]! md:max-w-[720px]! lg:max-w-[960px]! xl:max-w-[1140px]! 2xl:max-w-[1320px]!">

            <nav aria-label="breadcrumb" class="mb-4!">
                <ol class="breadcrumb text-[13px]! mb-0!">
                    <li class="breadcrumb-item">
                        <a href="{{ lroute('home') }}" class="text-[#cc1f1f]!">{{ __('show.breadcrumb_home') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $heading ?? __('list.petitions') }}</li>
                </ol>
            </nav>

            <div class="bg-white! shadow-sm! rounded-lg! p-4! border! border-[#eee]!">

                <div class="flex! items-center! justify-between! flex-wrap! gap-2! mb-2!">
                    <h1 class="text-base! leading-normal! m-0! font-bold! break-words!">{{ $heading ?? __('list.petitions') }}</h1>
                    <form method="GET" action="" data-live-search class="flex! gap-2! max-w-[300px]! w-full!">
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="{{ __('list.search_placeholder') }}"
                               aria-label="{{ __('list.search') }}"
                               class="block! w-full! px-2! py-1! text-[.875rem]! leading-normal! text-[#212529]! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-[#86b7fe]! focus:outline-none! focus:ring-4! focus:ring-[#0d6efd]/25!">
                        <button type="submit" class="inline-flex! items-center! justify-center! px-2! py-1! bg-[#cc1f1f]! text-white! border! border-[#cc1f1f]! rounded-md! cursor-pointer! hover:bg-[#b31b1b]!" aria-label="{{ __('list.search') }}">
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </button>
                        @if(request('q'))
                            <a href="{{ request()->url() }}" class="inline-flex! items-center! justify-center! px-2! py-1! text-[#6c757d]! bg-white! border! border-[#6c757d]! rounded-md! no-underline! hover:bg-[#6c757d]! hover:text-white!"
                               aria-label="{{ __('list.clear') }}" title="{{ __('list.clear') }}">
                                <i class="fa fa-times" aria-hidden="true"></i>
                            </a>
                        @endif
                    </form>
                </div>

                <div class="h-0.5! bg-[#e9ecef]! relative! mb-[22px]!">
                    <div
                        class="h-0.5! w-full! bg-[linear-gradient(to_right,#000,#f00)]! absolute! left-0! top-0!">
                    </div>
                </div>

                @if (! $search && isset($sort))
                    <div class="flex! items-center! flex-wrap! gap-2! mb-4!">
                        <span class="text-[13px]! text-[#6c757d]! me-1!">{{ __('list.sort_by') }}:</span>
                        @php
                            $sortBase = request()->except(['sort', 'page']);
                        @endphp
                        <a href="{{ request()->fullUrlWithQuery(array_merge($sortBase, ['sort' => 'newest'])) }}"
                           class="inline-flex! items-center! px-3! py-1! rounded-full! text-[13px]! no-underline! border! transition-colors! duration-150! {{ $sort === 'newest' ? 'bg-[#cc1f1f]! text-white! border-[#cc1f1f]!' : 'bg-white! text-[#cc1f1f]! border-[#e9ecef]! hover:border-[#cc1f1f]!' }}"
                           @if ($sort === 'newest') aria-current="true" @endif>{{ __('list.sort_newest') }}</a>
                        <a href="{{ request()->fullUrlWithQuery(array_merge($sortBase, ['sort' => 'signed'])) }}"
                           class="inline-flex! items-center! px-3! py-1! rounded-full! text-[13px]! no-underline! border! transition-colors! duration-150! {{ $sort === 'signed' ? 'bg-[#cc1f1f]! text-white! border-[#cc1f1f]!' : 'bg-white! text-[#cc1f1f]! border-[#e9ecef]! hover:border-[#cc1f1f]!' }}"
                           @if ($sort === 'signed') aria-current="true" @endif>{{ __('list.sort_signed') }}</a>
                    </div>
                @endif

                <div class="flex! flex-col! gap-2!">
                    @forelse($petitions as $petition)
                        <a href="{{ lroute('petition.show', [
                            'slug' => $petition->tr_slug,
                            'id' => $petition->id,
                        ]) }}" class="flex! justify-between! items-center! px-3.5! py-2.5! border! border-[#e9ecef]! rounded-md! text-[#cc1f1f]! text-sm! bg-white! transition-all! duration-[0.18s]! ease-[ease]! hover:bg-[#cc1f1f]! hover:text-white! hover:border-[#cc1f1f]! hover:shadow-[0_2px_6px_rgba(204,31,31,0.25)]! hover:no-underline!">
                            <span class="break-words! min-w-0!">{{ $petition->tr_title }}</span>
                            <span class="text-[#6c757d]! ms-4! text-nowrap! text-[13px]!">
                                <span data-countup data-countup-to="{{ (int) ($petition->signature_count ?? 0) }}">{{ number_format($petition->signature_count ?? 0) }}</span> {{ __('list.signatures') }}
                            </span>
                        </a>
                    @empty
                        <div class="flex! flex-col! items-center! justify-center! text-center! py-12! px-4!">
                            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#cc1f1f" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="mb-4! opacity-80!">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <path d="M14 2v6h6"></path>
                                <line x1="12" y1="18" x2="12" y2="12"></line>
                                <line x1="9" y1="15" x2="15" y2="15"></line>
                            </svg>
                            <p class="text-[#6c757d]! mb-4!">{{ __('list.none') }}</p>
                            <a href="{{ lroute('petition.create') }}" class="inline-flex! items-center! justify-center! px-4! py-2! bg-[#cc1f1f]! text-white! border! border-[#cc1f1f]! rounded-md! no-underline! hover:bg-[#b31b1b]! hover:no-underline!">
                                {{ __('home.btn_create_petition') }}
                            </a>
                        </div>
                    @endforelse
                </div>

                @include('partials.pagination', ['paginator' => $petitions])
            </div>

            <x-ad-unit placement="listing_inline" class="mt-6!" />

        </div>
    </section>

    <button type="button" class="fcui-scrolltop" data-scroll-top aria-label="Back to top">↑</button>
@endsection
