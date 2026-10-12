@extends('layouts.legacy')

@section('title', ($pageTitle ?? __('list.petitions')) . ' - FreeCause')

@section('content')
    <section class="fc-section">
        <x-ui.container>

            <nav aria-label="breadcrumb" class="mb-4!">
                <ol class="breadcrumb text-[13px]! mb-0!">
                    <li class="breadcrumb-item">
                        <a href="{{ lroute('home') }}" class="text-brand!">{{ __('show.breadcrumb_home') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $heading ?? __('list.petitions') }}</li>
                </ol>
            </nav>

            <div class="bg-white! shadow-sm! rounded-lg! p-4! border! border-border!">

                <div class="flex! items-center! justify-between! flex-wrap! gap-2! mb-2!">
                    <h1 class="text-base! leading-normal! m-0! font-bold! break-words!">{{ $heading ?? __('list.petitions') }}</h1>
                    <form method="GET" action="" data-live-search class="flex! gap-2! max-w-[300px]! w-full!">
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="{{ __('list.search_placeholder') }}"
                               aria-label="{{ __('list.search') }}"
                               class="block! w-full! px-2! py-1! text-[.875rem]! leading-normal! text-ink! bg-white! border! border-[#ced4da]! rounded-md! appearance-none! focus:border-brand-light! focus:outline-none! focus:ring-4! focus:ring-brand/25!">
                        <button type="submit" class="inline-flex! items-center! justify-center! px-2! py-1! bg-brand! text-white! border! border-brand! rounded-md! cursor-pointer! hover:bg-[#b31b1b]!" aria-label="{{ __('list.search') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </button>
                        @if(request('q'))
                            <a href="{{ request()->url() }}" class="inline-flex! items-center! justify-center! px-2! py-1! text-muted! bg-white! border! border-muted! rounded-md! no-underline! hover:bg-muted! hover:text-white!"
                               aria-label="{{ __('list.clear') }}" title="{{ __('list.clear') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </a>
                        @endif
                    </form>
                </div>

                <div class="h-0.5! bg-border! relative! mb-[22px]!">
                    <div
                        class="h-0.5! w-full! bg-[linear-gradient(to_right,#000,#f00)]! absolute! left-0! top-0!">
                    </div>
                </div>

                @if (! $search && isset($sort))
                    <div class="flex! items-center! flex-wrap! gap-2! mb-4!">
                        <span class="text-[13px]! text-muted! me-1!">{{ __('list.sort_by') }}:</span>
                        @php
                            $sortBase = request()->except(['sort', 'page']);
                        @endphp
                        <a href="{{ request()->fullUrlWithQuery(array_merge($sortBase, ['sort' => 'newest'])) }}"
                           class="inline-flex! items-center! px-3! py-1! rounded-full! text-[13px]! no-underline! border! transition-colors! duration-150! {{ $sort === 'newest' ? 'bg-brand! text-white! border-brand!' : 'bg-white! text-brand! border-border! hover:border-brand!' }}"
                           @if ($sort === 'newest') aria-current="true" @endif>{{ __('list.sort_newest') }}</a>
                        <a href="{{ request()->fullUrlWithQuery(array_merge($sortBase, ['sort' => 'signed'])) }}"
                           class="inline-flex! items-center! px-3! py-1! rounded-full! text-[13px]! no-underline! border! transition-colors! duration-150! {{ $sort === 'signed' ? 'bg-brand! text-white! border-brand!' : 'bg-white! text-brand! border-border! hover:border-brand!' }}"
                           @if ($sort === 'signed') aria-current="true" @endif>{{ __('list.sort_signed') }}</a>
                    </div>
                @endif

                <div class="flex! flex-col! gap-2! fc-reveal fc-stagger">
                    @forelse($petitions as $petition)
                        <a href="{{ lroute('petition.show', [
                            'slug' => $petition->tr_slug,
                            'id' => $petition->id,
                        ]) }}" data-prefetch style="view-transition-name: petition-{{ $petition->id }}" class="flex! justify-between! items-center! px-3.5! py-2.5! border! border-border! rounded-md! text-brand! text-sm! bg-white! transition-all! duration-[0.18s]! ease-[ease]! hover:bg-brand! hover:text-white! hover:border-brand! hover:shadow-[0_2px_6px_rgba(204,31,31,0.25)]! hover:-translate-y-px! hover:no-underline!">
                            <span class="break-words! min-w-0!">{{ $petition->tr_title }}</span>
                            <span class="fc-tabular text-muted! ms-4! text-nowrap! text-[13px]!">
                                <span data-abbr data-abbr-value="{{ (int) ($petition->signature_count ?? 0) }}">{{ number_format($petition->signature_count ?? 0) }}</span> {{ __('list.signatures') }}
                            </span>
                        </a>
                    @empty
                        <div class="flex! flex-col! items-center! justify-center! text-center! py-12! px-4!">
                            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120" fill="none" aria-hidden="true" class="mb-4!">
                                <circle cx="60" cy="60" r="56" fill="#fff5f5"></circle>
                                <circle cx="60" cy="60" r="56" stroke="#f6d4d4" stroke-width="2"></circle>
                                <rect x="38" y="30" width="44" height="56" rx="4" fill="#ffffff" stroke="#cc1f1f" stroke-width="2.5"></rect>
                                <path d="M66 30v12h12" fill="#fff5f5" stroke="#cc1f1f" stroke-width="2.5" stroke-linejoin="round"></path>
                                <line x1="46" y1="52" x2="74" y2="52" stroke="#f6a5a5" stroke-width="2.5" stroke-linecap="round"></line>
                                <line x1="46" y1="62" x2="74" y2="62" stroke="#f6a5a5" stroke-width="2.5" stroke-linecap="round"></line>
                                <line x1="46" y1="72" x2="62" y2="72" stroke="#f6a5a5" stroke-width="2.5" stroke-linecap="round"></line>
                                <circle cx="80" cy="82" r="16" fill="#cc1f1f"></circle>
                                <path d="M74 82h12M80 76v12" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round"></path>
                            </svg>
                            <p class="text-muted! mb-4!">{{ __('list.none') }}</p>
                            <a href="{{ lroute('petition.create') }}" class="inline-flex! items-center! justify-center! px-4! py-2! bg-brand! text-white! border! border-brand! rounded-md! no-underline! hover:bg-[#b31b1b]! hover:no-underline!">
                                {{ __('home.btn_create_petition') }}
                            </a>
                        </div>
                    @endforelse
                </div>

                @include('partials.pagination', ['paginator' => $petitions])
            </div>

            <x-ad-unit placement="listing_inline" class="mt-6!" />

        </x-ui.container>
    </section>

    <button type="button" class="fcui-scrolltop" data-scroll-top aria-label="Back to top">↑</button>
@endsection
