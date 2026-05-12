@extends('layouts.legacy')

@section('title', ($pageTitle ?? 'Petitions') . ' - xPetition')

@section('content')
    <section class="py-5">
        <div class="container">

            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb" style="font-size:13px;margin-bottom:0;">
                    <li class="breadcrumb-item">
                        <a href="{{ lroute('home') }}" class="red">{{ __('show.breadcrumb_home') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $heading ?? 'Petitions' }}</li>
                </ol>
            </nav>

            <div class="bg-white shadow-sm rounded-3 p-4" style="border:1px solid #eee;">

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <div style="font-weight:700;">{{ $heading ?? 'Petitions' }}</div>
                    <form method="GET" action="" class="d-flex gap-2" style="max-width:300px;width:100%;">
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="{{ __('Search petitions…') }}"
                               class="form-control form-control-sm">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fa fa-search"></i>
                        </button>
                        @if(request('q'))
                            <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary">✕</a>
                        @endif
                    </form>
                </div>

                <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                    <div
                        style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                    </div>
                </div>

                <div class="fc-petitions-list">
                    @forelse($petitions as $petition)
                        <a href="{{ lroute('petition.show', [
                            'slug' => $petition->tr_slug,
                            'id' => $petition->id,
                        ]) }}" class="fc-petition-row d-flex justify-content-between align-items-center">
                            <span>{{ $petition->tr_title }}</span>
                            <span class="text-muted ms-3 text-nowrap" style="font-size:13px;">
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
