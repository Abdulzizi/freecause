@extends('layouts.legacy')

@section('title', 'My petitions - FreeCause')

@section('content')
    <section class="py-4">
        <div class="container">
            <div class="bg-white! border border-[#e9e9e9]! rounded-[3px]! shadow-[0_2px_12px_rgba(0,0,0,0.10)]! overflow-hidden! mt-5!">
                <div class="row g-0">

                    <div class="col-md-6 border-r border-[#eee]! px-[26px]! py-[22px]! min-h-[260px]!">
                        <div class="text-[26px]! font-medium! mb-[18px]! text-[#222]!">{{ __('myp.signed_heading') }}</div>

                        <div class="max-w-[520px]!">
                            @forelse($signed as $p)
                                @php
                                    $date = $p->signed_at ? \Carbon\Carbon::parse($p->signed_at) : $p->created_at;
                                @endphp
                                <a class="flex! items-center! justify-between! gap-4! no-underline! bg-[#fffafa]! border border-[#f0d7d7]! rounded-md! px-3! py-2.5! mb-2.5! text-[#222]! hover:bg-[#fff3f3]!"
                                    href="{{ route('petition.show', ['locale' => $locale, 'slug' => $p->tr_slug, 'id' => $p->id]) }}">
                                    <div class="text-[15px]! leading-[1.2]! flex-1!">{{ $p->tr_title }}</div>
                                    <div class="text-[15px]! text-[#222]! whitespace-nowrap!">{{ $date->format('j F Y') }}</div>
                                </a>
                            @empty
                                <div class="text-sm! text-[#777]! py-2.5! px-0!">{{ __('myp.signed_empty') }}</div>
                            @endforelse

                            @if($tab === 'signed' && $signed instanceof \Illuminate\Contracts\Pagination\Paginator)
                                <div class="mt-3">
                                    {{ $signed->links() }}
                                </div>
                            @else
                                <a class="flex! items-center! justify-start! gap-4! no-underline! bg-[#fffafa]! border border-[#f0d7d7]! rounded-md! px-3! py-2.5! mb-2.5! text-[#222]! text-[15px]! hover:bg-[#fff3f3]!"
                                    href="{{ lroute('account.petitions', ['tab' => 'signed']) }}">
                                    {{ __('myp.full_list') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6 px-[26px]! py-[22px]! min-h-[260px]!">
                        <div class="text-[26px]! font-medium! mb-[18px]! text-[#222]!">{{ __('myp.created_heading') }}</div>

                        <div class="max-w-[520px]!">
                            @forelse($created as $p)
                                <a class="flex! items-center! justify-between! gap-4! no-underline! bg-[#fffafa]! border border-[#f0d7d7]! rounded-md! px-3! py-2.5! mb-2.5! text-[#222]! hover:bg-[#fff3f3]!"
                                    href="{{ route('petition.show', ['locale' => $locale, 'slug' => $p->tr_slug, 'id' => $p->id]) }}">
                                    <div class="text-[15px]! leading-[1.2]! flex-1!">{{ $p->tr_title }}</div>
                                    <div class="text-[15px]! text-[#222]! whitespace-nowrap!">{{ optional($p->created_at)->format('j F Y') }}</div>
                                </a>
                            @empty
                                <div class="text-sm! text-[#777]! py-2.5! px-0!">{{ __('myp.created_empty') }}</div>
                            @endforelse

                            @if($tab === 'created' && $created instanceof \Illuminate\Contracts\Pagination\Paginator)
                                <div class="mt-3">
                                    {{ $created->links() }}
                                </div>
                            @else
                                <a class="flex! items-center! justify-start! gap-4! no-underline! bg-[#fffafa]! border border-[#f0d7d7]! rounded-md! px-3! py-2.5! mb-2.5! text-[#222]! text-[15px]! hover:bg-[#fff3f3]!"
                                    href="{{ lroute('account.petitions', ['tab' => 'created']) }}">
                                    {{ __('myp.full_list') }}
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
