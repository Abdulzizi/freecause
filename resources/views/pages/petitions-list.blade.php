@extends('layouts.legacy')

@section('title', ($pageTitle ?? 'Petitions') . ' - FreeCause')

@section('content')
    <section class="py-5">
        <div class="container">

            {{-- <div class="mb-4">
                <h1 class="mb-2" style="font-size:24px;font-weight:600;">
                    {{ $heading ?? ($pageTitle ?? 'Petitions') }}
                </h1>
            </div> --}}

            <div class="bg-white shadow-sm rounded-3 p-4" style="border:1px solid #eee;">

                <div class="mb-2" style="font-weight:700;">Petitions</div>
                <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                    <div
                        style="height:2px;width:100%;background:linear-gradient(to right, black, red);position:absolute;left:0;top:0;">
                    </div>
                </div>

                <div class="fc-petitions-list">
                    @forelse($petitions as $petition)
                        <a href="{{ $petitionUrl($petition) }}" class="fc-petition-row">
                            {{ $petitionTitle($petition) }}
                        </a>
                    @empty
                        <div class="text-muted">no petitions found.</div>
                    @endforelse
                </div>

                {{-- pagination only if it's a paginator --}}
                @if ($petitions instanceof \Illuminate\Contracts\Pagination\Paginator && $petitions->hasPages())
                    <div class="d-flex gap-1 mt-4 flex-wrap">
                        @if ($petitions->onFirstPage())
                            <span class="fc-page disabled">«</span>
                        @else
                            <a class="fc-page" href="{{ $petitions->previousPageUrl() }}">«</a>
                        @endif

                        @foreach ($petitions->getUrlRange(1, $petitions->lastPage()) as $page => $url)
                            @if ($page == $petitions->currentPage())
                                <span class="fc-page active">{{ $page }}</span>
                            @else
                                <a class="fc-page" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($petitions->hasMorePages())
                            <a class="fc-page" href="{{ $petitions->nextPageUrl() }}">»</a>
                        @else
                            <span class="fc-page disabled">»</span>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    </section>
@endsection
