@extends('layouts.legacy')

@section('title', ($pageTitle ?? 'Petitions') . ' - FreeCause')

@section('content')
    <section class="py-5">
        <div class="container">

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

                @include('partials.pagination', ['paginator' => $petitions])
            </div>
        </div>
    </section>
@endsection
