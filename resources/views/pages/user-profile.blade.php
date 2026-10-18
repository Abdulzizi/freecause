@extends('layouts.legacy')

@section('title', $user->display_name . ' - FreeCause')

@section('content')

    <section class="fc-section">
        <x-ui.container>

            {{-- Ads: leaderboard (desktop) --}}
            <div class="text-center mb-4 hidden lg:block">
                <x-ads.slot name="leaderboard" />
            </div>
            {{-- Ads: mobile banner --}}
            <div class="mb-4 text-center lg:hidden">
                <x-ads.slot name="mobile" />
            </div>

            <div class="mb-6">
                <h1 class="mb-2 text-2xl font-semibold break-words">
                    {{ $user->display_name }}
                </h1>
                <div class="text-sm">
                    <a class="text-brand" href="/{{ app()->getLocale() }}">{{ __('userprofile.home') }}</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted break-words">{{ $user->display_name }}</span>
                </div>
            </div>

            <div class="bg-white shadow-elev-1 rounded-lg p-4">

                <div class="fcui-section-title capitalize text-xl md:text-2xl font-bold break-words">{{ $user->display_name }}</div>

                <div class="flex flex-wrap -mx-3">

                    <div class="w-full md:w-1/3 shrink-0 grow-0 px-3">
                        <table class="w-full text-sm border-collapse fc-tabular">
                            <tr class="border-b border-border">
                                <td class="py-2.5 text-muted">{{ __('userprofile.registration_date') }}</td>
                                <td class="py-2.5 text-right font-medium">
                                    {{ optional($user->created_at)->format('n/j/y H:i') }}
                                </td>
                            </tr>
                            <tr class="border-b border-border">
                                <td class="py-2.5 text-muted">{{ __('userprofile.petitions') }}</td>
                                <td class="py-2.5 text-right font-medium">
                                    {{ $petitionCount }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2.5 text-muted">{{ __('userprofile.signatures') }}</td>
                                <td class="py-2.5 text-right font-medium">
                                    {{ $signatureCount }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="w-full md:w-2/3 shrink-0 grow-0 px-3">

                        @php($tab = request('tab') === 'signed' ? 'signed' : 'created')
                        <div class="flex gap-4 mb-4 text-sm font-bold">
                            <a href="?tab=created">{{ __('userprofile.created_tab') }}</a>
                            <a href="?tab=signed">{{ __('userprofile.signed_tab') }}</a>
                        </div>

                        @if ($tab === 'created')
                        <div class="mb-6 fc-reveal fc-stagger">
                            <h2 class="text-base font-bold mb-4 break-words">
                                {{ __('userprofile.recent_petitions', ['name' => $user->display_name]) }}
                            </h2>

                            @forelse ($recentPetitions as $p)
                                <div class="border-b border-border text-sm" style="--fc-i: {{ min($loop->index, 12) }}">
                                    <a class="py-1.5 max-md:flex max-md:items-center max-md:min-h-12 text-brand break-words" href="{{ route('petition.show', ['slug' => $p->slug, 'id' => $p->id]) }}">
                                        {{ $p->title }}
                                    </a>
                                </div>
                            @empty
                                <x-ui.empty :text="__('userprofile.no_petitions')" class="px-4 py-6" />
                            @endforelse
                        </div>

                        @else
                        <div class="fc-reveal fc-stagger">
                            <h2 class="text-base font-bold mb-4 break-words">
                                {{ __('userprofile.recent_signatures', ['name' => $user->display_name]) }}
                            </h2>

                            @forelse ($recentSignatures as $s)
                                <div class="border-b border-border text-sm" style="--fc-i: {{ min($loop->index, 12) }}">
                                    <a class="py-1.5 max-md:flex max-md:items-center max-md:min-h-12 text-brand break-words" href="{{ route('petition.show', ['slug' => $s->slug, 'id' => $s->petition_id]) }}">
                                        {{ $s->title }}
                                    </a>
                                </div>
                            @empty
                                <div class="text-sm text-muted">{{ __('userprofile.no_signatures') }}</div>
                            @endforelse
                        </div>
                        @endif

                    </div>
                </div>

            </div>

            {{-- Ads: rectangle --}}
            <div class="mb-4 text-center">
                <x-ads.slot name="rectangle" />
            </div>

        </x-ui.container>
    </section>

@endsection
