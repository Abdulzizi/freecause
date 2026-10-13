@extends('layouts.legacy')

@section('title', 'My petitions - FreeCause')

@section('content')
    <x-ui.section>
        <div class="bg-white border border-border rounded-sm shadow-elev-2 overflow-hidden mt-5">
            <div class="flex flex-wrap gap-0">

                    <div class="w-full md:w-1/2 shrink-0 border-r border-border px-6 py-6 min-h-[260px]">
                        <h1 class="m-0 text-2xl font-medium mb-4 text-ink">{{ __('myp.signed_heading') }}</h1>

                        <div class="max-w-[520px] fc-reveal fc-stagger">
                            @forelse($signed as $p)
                                @php
                                    $date = $p->signed_at ? \Carbon\Carbon::parse($p->signed_at) : $p->created_at;
                                @endphp
                                <a class="flex max-md:min-h-12 items-center justify-between gap-4 no-underline bg-surface border border-brand-border rounded-md px-3 py-2.5 mb-2.5 text-ink hover:bg-brand-soft fc-lift" style="--fc-i: {{ min($loop->index, 12) }}">
                                    href="{{ route('petition.show', ['locale' => $locale, 'slug' => $p->tr_slug, 'id' => $p->id]) }}">
                                    <div class="text-sm leading-[1.2] flex-1">{{ $p->tr_title }}</div>
                                    <div class="text-sm text-ink whitespace-nowrap fc-tabular">{{ $date->format('j F Y') }}</div>
                                </a>
                            @empty
                                <div class="text-sm text-muted py-2.5 px-0">{{ __('myp.signed_empty') }}</div>
                            @endforelse

                            @if($tab === 'signed' && $signed instanceof \Illuminate\Contracts\Pagination\Paginator)
                                <div class="mt-4">
                                    {{ $signed->links() }}
                                </div>
                            @else
                                <a class="flex max-md:min-h-12 items-center justify-start gap-4 no-underline bg-surface border border-brand-border rounded-md px-3 py-2.5 mb-2.5 text-ink text-sm hover:bg-brand-soft"
                                    href="{{ lroute('account.petitions', ['tab' => 'signed']) }}">
                                    {{ __('myp.full_list') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="w-full md:w-1/2 shrink-0 px-6 py-6 min-h-[260px]">
                        <h2 class="m-0 text-2xl font-medium mb-4 text-ink">{{ __('myp.created_heading') }}</h2>

                        <div class="max-w-[520px] fc-reveal fc-stagger">
                            @forelse($created as $p)
                                <a class="flex max-md:min-h-12 items-center justify-between gap-4 no-underline bg-surface border border-brand-border rounded-md px-3 py-2.5 mb-2.5 text-ink hover:bg-brand-soft fc-lift" style="--fc-i: {{ min($loop->index, 12) }}"
                                    href="{{ route('petition.show', ['locale' => $locale, 'slug' => $p->tr_slug, 'id' => $p->id]) }}">
                                    <div class="text-sm leading-[1.2] flex-1">{{ $p->tr_title }}</div>
                                    <div class="text-sm text-ink whitespace-nowrap fc-tabular">{{ optional($p->created_at)->format('j F Y') }}</div>
                                </a>
                            @empty
                                <div class="text-sm text-muted py-2.5 px-0">{{ __('myp.created_empty') }}</div>
                            @endforelse

                            @if($tab === 'created' && $created instanceof \Illuminate\Contracts\Pagination\Paginator)
                                <div class="mt-4">
                                    {{ $created->links() }}
                                </div>
                            @else
                                <a class="flex max-md:min-h-12 items-center justify-start gap-4 no-underline bg-surface border border-brand-border rounded-md px-3 py-2.5 mb-2.5 text-ink text-sm hover:bg-brand-soft"
                                    href="{{ lroute('account.petitions', ['tab' => 'created']) }}">
                                    {{ __('myp.full_list') }}
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
    </x-ui.section>
@endsection
