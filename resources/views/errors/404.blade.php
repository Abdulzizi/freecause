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
                <h1 class="mb-2" style="font-size:24px; font-weight:600;">Page not found</h1>
                <div style="font-size:14px;">
                    <a class="red" href="{{ url('/'.app()->getLocale()) }}">Home</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">404</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4 mb-5" style="border:1px solid #eee;">
                <div class="mb-2 headings">404</div>

                <p style="font-size:15px; color:#555; margin-bottom:6px;">
                    Error 404: page not found.
                </p>
                <p style="font-size:15px; color:#555; margin-bottom:20px;">
                    The requested page was not found.
                </p>
                <p style="font-size:14px; color:#555; margin-bottom:20px;">
                    However you may find interesting the following petitions:
                </p>

                @if ($mostRead->isNotEmpty())
                        <h2 class="" style="font-size:18px; font-weight:700; margin-bottom:16px;">
                            Most Read Month Petitions
                        </h2>

                        <div class="row g-3">
                            @foreach ($mostRead as $p)
                                <div class="col-md-6">
                                    <div style="padding:10px; border:1px solid #f0f0f0; border-radius:6px;">
                                        <a class="red" style="font-size:14px; font-weight:500; text-decoration:none;"
                                            href="{{ route('petition.show', [
                                                'locale' => $locale,
                                                'slug' => $p->slug,
                                                'id' => $p->id,
                                            ]) }}">
                                            {{ $p->title }}
                                        </a>
                                        <div style="font-size:12px; color:#999; margin-top:4px;">
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
