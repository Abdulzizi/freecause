@extends('layouts.legacy')

@section('title', 'My petitions - FreeCause')

@section('content')
    {{-- ad: leaderboard, desktop only --}}
    <div class="mb-4 text-center hidden lg:block">
        <x-ads.slot name="leaderboard" />
    </div>
    {{-- ad: mobile banner, mobile only --}}
    <div class="mb-4 text-center lg:hidden">
        <x-ads.slot name="mobile" />
    </div>

    <x-ui.section>
        <div class="bg-white border border-border rounded-sm shadow-elev-2 overflow-hidden mt-5">
            <div class="flex flex-wrap gap-0">

                    <div class="w-full md:w-1/2 shrink-0 border-r border-border px-6 py-6 min-h-[260px]">
                        <h1 class="m-0 text-2xl font-medium mb-4 text-ink">{{ __('myp.signed_heading') }}</h1>

                        <div class="max-w-[520px] fc-reveal fc-stagger">
                            @forelse($signed as $p)
                                @if ($loop->index > 0 && $loop->index % 2 === 0)
                                    @php($signedAdZones = ['mobile_feed', 'mobile_inline', 'mobile_native_small'])
                                    @include('partials.ads-infeed', ['slot' => $signedAdZones[(int) ($loop->index / 2) % count($signedAdZones)]])
                                @endif
                                @php
                                    $date = $p->signed_at ? \Carbon\Carbon::parse($p->signed_at) : $p->created_at;
                                @endphp
                                <a class="flex max-md:min-h-12 items-center justify-between gap-4 no-underline bg-surface border border-brand-border rounded-md px-3 py-2.5 mb-2.5 text-ink hover:bg-brand-soft fc-lift" style="--fc-i: {{ min($loop->index, 12) }}">
                                    href="{{ route('petition.show', ['locale' => $locale, 'slug' => $p->tr_slug, 'id' => $p->id]) }}">
                                    <div class="text-sm leading-[1.2] flex-1">{{ $p->tr_title }}</div>
                                    <div class="text-sm text-ink whitespace-nowrap fc-tabular">{{ $date->format('j F Y') }}</div>
                                </a>
                            @empty
                                <x-ui.empty :text="__('myp.signed_empty')" class="fcui-empty--compact" />
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
                                @if ($loop->index > 0 && $loop->index % 2 === 0)
                                    @php($createdAdZones = ['mobile_mini', 'mobile_banner', 'mobile_card'])
                                    @include('partials.ads-infeed', ['slot' => $createdAdZones[(int) ($loop->index / 2) % count($createdAdZones)]])
                                @endif
                                <a class="flex max-md:min-h-12 items-center justify-between gap-4 no-underline bg-surface border border-brand-border rounded-md px-3 py-2.5 mb-2.5 text-ink hover:bg-brand-soft fc-lift" style="--fc-i: {{ min($loop->index, 12) }}"
                                    href="{{ route('petition.show', ['locale' => $locale, 'slug' => $p->tr_slug, 'id' => $p->id]) }}">
                                    <div class="text-sm leading-[1.2] flex-1">{{ $p->tr_title }}</div>
                                    <div class="text-sm text-ink whitespace-nowrap fc-tabular">{{ optional($p->created_at)->format('j F Y') }}</div>
                                </a>
                            @empty
                                <x-ui.empty :text="__('myp.created_empty')" class="fcui-empty--compact" />
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
            @include('partials.ads-mini', ['slot' => 'mobile_strip'])
            @include('partials.ads-mini', ['slot' => 'mobile_native_small'])
    </x-ui.section>
@endsection
