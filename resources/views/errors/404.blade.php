@extends('layouts.legacy')

@section('title', '404 - Page not found')

@section('content')
    <section class="py-5">
        <div class="container">

            @php
                $locale = app()->getLocale();
                $mostRead = \Illuminate\Support\Facades\DB::table('petitions as p')
                    ->join('petition_translations as pt', function ($j) use ($locale) {
                        $j->on('pt.petition_id', '=', 'p.id')->where('pt.locale', '=', $locale);
                    })
                    ->where('p.status', 'published')
                    ->where('p.is_active', 1)
                    ->select(['p.id', 'pt.title', 'pt.slug', 'p.signature_count'])
                    ->orderByDesc('p.signature_count')
                    ->limit(6)
                    ->get();
            @endphp

            <div class="mb-4">
                <h1 class="mb-2 text-2xl! font-semibold!">Page not found</h1>
                <div class="text-sm!">
                    <a class="text-brand!" href="{{ url('/'.app()->getLocale()) }}">Home</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">404</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4 mb-5 border! border-[#eee]!">
                <div class="mb-2 relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0!">404</div>

                <p class="text-[15px]! text-[#555]! mb-1.5!">
                    Error 404: page not found.
                </p>
                <p class="text-[15px]! text-[#555]! mb-5!">
                    The requested page was not found.
                </p>
                <p class="text-sm! text-[#555]! mb-5!">
                    However you may find interesting the following petitions:
                </p>

                @if ($mostRead->isNotEmpty())
                        <h2 class="text-lg! font-bold! mb-4!">
                            Most Read Month Petitions
                        </h2>

                        <div class="row g-3">
                            @foreach ($mostRead as $p)
                                <div class="col-md-6">
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
                                            {{ number_format($p->signature_count) }} signatures
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
