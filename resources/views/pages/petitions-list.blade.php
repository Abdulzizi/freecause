@extends('layouts.legacy')

@section('title', ($pageTitle ?? 'Petitions') . ' - FreeCause')

@section('content')
    <section class="py-16!">
        <div class="container">

            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb text-[13px]! mb-0!">
                    <li class="breadcrumb-item">
                        <a href="{{ lroute('home') }}" class="text-[#cc1f1f]!">{{ __('show.breadcrumb_home') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $heading ?? 'Petitions' }}</li>
                </ol>
            </nav>

            <div class="bg-white shadow-sm rounded-3 p-4 border border-[#eee]!">

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <h1 class="text-base! leading-normal! m-0! font-bold!">{{ $heading ?? 'Petitions' }}</h1>
                    <form method="GET" action="" class="d-flex gap-2 max-w-[300px]! w-full!">
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="{{ __('Search petitions…') }}"
                               aria-label="{{ __('Search petitions') }}"
                               class="form-control form-control-sm">
                        <button type="submit" class="btn btn-sm btn-danger" aria-label="{{ __('Search') }}">
                            <i class="fa fa-search"></i>
                        </button>
                        @if(request('q'))
                            <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center"
                               aria-label="{{ __('Clear search') }}" title="{{ __('Clear search') }}">
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
                        ]) }}" class="d-flex justify-content-between align-items-center px-3.5! py-2.5! border border-[#e9ecef]! rounded-md! text-[#cc1f1f]! text-sm! bg-white! transition-all! duration-[0.18s]! ease-[ease]! hover:bg-[#cc1f1f]! hover:text-white! hover:border-[#cc1f1f]! hover:shadow-[0_2px_6px_rgba(204,31,31,0.25)]! hover:no-underline!">
                            <span>{{ $petition->tr_title }}</span>
                            <span class="text-muted ms-3 text-nowrap text-[13px]!">
                                {{ number_format($petition->signature_count ?? 0) }} signatures
                            </span>
                        </a>
                    @empty
                        <div class="text-muted">
                            {{ __('No petitions found.') }}
                        </div>
                    @endforelse
                </div>

                @include('partials.pagination', ['paginator' => $petitions])
            </div>

        </div>
    </section>
@endsection
