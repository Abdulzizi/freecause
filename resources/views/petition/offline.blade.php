@extends('layouts.legacy')

@section('title', $tr->title)

@section('content')
    <section class="py-14 md:py-24">
        <x-ui.container>
            <div class="bg-white shadow-elev-1 rounded-md p-8 md:p-12 text-center">
                <div class="mb-4"><x-ui.badge variant="warning">{{ __('pet.offline_badge') }}</x-ui.badge></div>
                <h1 class="mb-4">{{ $tr->title }}</h1>
                <p class="mb-2">{{ __('pet.offline_title') }}</p>

                <p class="mb-6 text-muted text-sm fc-tabular">
                    {{ __('pet.offline_closed_on', ['date' => optional($petition->updated_at)->format('j F Y')]) }}
                    <span class="px-2 text-subtle" aria-hidden="true">|</span>
                    {{ __('pet.total_signatures') }}
                    <span data-countup data-countup-to="{{ (int) ($petition->signature_count ?? 0) }}">{{ number_format($petition->signature_count ?? 0) }}</span>
                </p>

                <x-ui.button variant="primary" :href="lroute('petitions.index')">{{ __('pet.explore') }}</x-ui.button>
            </div>

            @if (($related ?? collect())->isNotEmpty())
                <div class="mt-10">
                    <h2 class="text-lg font-bold mb-4">{{ __('pet.offline_next') }}</h2>
                    <div class="flex flex-wrap -mx-3 fc-reveal fc-stagger">
                        @foreach ($related as $rel)
                            <div class="w-full md:w-1/3 shrink-0 px-3 mb-6" style="--fc-i: {{ min($loop->index, 12) }}">
                                <a href="{{ lroute('petition.show', ['slug' => $rel->tr_slug, 'id' => $rel->id]) }}"
                                   class="block h-full no-underline bg-white rounded-md shadow-elev-1 overflow-hidden fc-lift">
                                    <div class="p-4">
                                        <div class="text-ink font-semibold text-sm leading-snug break-words [overflow-wrap:anywhere]">{{ $rel->tr_title }}</div>
                                        <div class="mt-2 text-sm text-muted fc-tabular">
                                            <span data-countup data-countup-to="{{ (int) ($rel->signature_count ?? 0) }}">{{ number_format($rel->signature_count ?? 0) }}</span> {{ __('list.signatures') }}
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-ui.container>
    </section>
@endsection
