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
                    <form method="GET" action="" class="flex! gap-2! max-w-[300px]! w-full!">
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

                <div class="flex! flex-col! gap-2!">
                    @forelse($petitions as $petition)
                        <a href="{{ lroute('petition.show', [
                            'slug' => $petition->tr_slug,
                            'id' => $petition->id,
                        ]) }}" class="flex! justify-between! items-center! px-3.5! py-2.5! border! border-[#e9ecef]! rounded-md! text-[#cc1f1f]! text-sm! bg-white! transition-all! duration-[0.18s]! ease-[ease]! hover:bg-[#cc1f1f]! hover:text-white! hover:border-[#cc1f1f]! hover:shadow-[0_2px_6px_rgba(204,31,31,0.25)]! hover:no-underline!">
                            <span class="break-words! min-w-0!">{{ $petition->tr_title }}</span>
                            <span class="text-[#6c757d]! ms-4! text-nowrap! text-[13px]!">
                                {{ number_format($petition->signature_count ?? 0) }} {{ __('list.signatures') }}
                            </span>
                        </a>
                    @empty
                        <div class="text-[#6c757d]!">
                            {{ __('list.none') }}
                        </div>
                    @endforelse
                </div>

                @include('partials.pagination', ['paginator' => $petitions])
            </div>

        </div>
    </section>
@endsection
