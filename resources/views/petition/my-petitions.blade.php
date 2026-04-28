@extends('layouts.legacy')

@section('title', 'My petitions - xPetition')

@section('content')
    <section class="py-4">
        <div class="container">
            <div class="fc-myp-wrap">
                <div class="row g-0">

                    <div class="col-md-6 fc-myp-col fc-myp-left">
                        <div class="fc-myp-head">Recent Petitions I Have Signed</div>

                        <div class="fc-myp-list">
                            @forelse($signed as $p)
                                @php
                                    $date = $p->signed_at ? \Carbon\Carbon::parse($p->signed_at) : $p->created_at;
                                @endphp
                                <a class="fc-myp-item"
                                    href="{{ route('petition.show', ['locale' => $locale, 'slug' => $p->tr_slug, 'id' => $p->id]) }}">
                                    <div class="fc-myp-title">{{ $p->tr_title }}</div>
                                    <div class="fc-myp-date">{{ $date->format('j F Y') }}</div>
                                </a>
                            @empty
                                <div class="fc-myp-empty">No signed petitions yet.</div>
                            @endforelse

                            @if($tab === 'signed' && $signed instanceof \Illuminate\Contracts\Pagination\Paginator)
                                <div class="mt-3">
                                    {{ $signed->links() }}
                                </div>
                            @else
                                <a class="fc-myp-item fc-myp-full"
                                    href="{{ lroute('account.petitions', ['tab' => 'signed']) }}">
                                    » full list
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6 fc-myp-col fc-myp-right">
                        <div class="fc-myp-head">Recent Petitions I Have Created</div>

                        <div class="fc-myp-list">
                            @forelse($created as $p)
                                <a class="fc-myp-item"
                                    href="{{ route('petition.show', ['locale' => $locale, 'slug' => $p->tr_slug, 'id' => $p->id]) }}">
                                    <div class="fc-myp-title">{{ $p->tr_title }}</div>
                                    <div class="fc-myp-date">{{ optional($p->created_at)->format('j F Y') }}</div>
                                </a>
                            @empty
                                <div class="fc-myp-empty">No created petitions yet.</div>
                            @endforelse

                            @if($tab === 'created' && $created instanceof \Illuminate\Contracts\Pagination\Paginator)
                                <div class="mt-3">
                                    {{ $created->links() }}
                                </div>
                            @else
                                <a class="fc-myp-item fc-myp-full"
                                    href="{{ lroute('account.petitions', ['tab' => 'created']) }}">
                                    » full list
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
