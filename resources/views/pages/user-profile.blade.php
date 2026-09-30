@extends('layouts.legacy')

@section('title', $user->display_name . ' - FreeCause')

@section('content')

    <section class="py-5">
        <div class="container">

            <div class="mb-4">
                <h1 class="mb-2 text-2xl! font-semibold!">
                    {{ $user->display_name }}
                </h1>
                <div class="text-sm!">
                    <a class="text-brand!" href="/{{ app()->getLocale() }}">Home</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">{{ $user->display_name }}</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4 border! border-[#eee]!">

                <div class="mb-4 relative! capitalize! text-lg! pb-2.5! mb-5! font-semibold! after:absolute! after:content-['']! after:h-px! after:bg-[linear-gradient(to_right,#000_30%,#ce2020_30%)]! after:w-full! after:left-0! after:bottom-0! font-bold!">{{ $user->display_name }}</div>

                <div class="row g-4">

                    <div class="col-md-4">
                        <table class="w-full! text-sm! border-collapse!">
                            <tr class="border-b! border-[#eee]!">
                                <td class="py-2.5! text-[#555]!">Registration date :</td>
                                <td class="py-2.5! text-right! font-medium!">
                                    {{ optional($user->created_at)->format('n/j/y H:i') }}
                                </td>
                            </tr>
                            <tr class="border-b! border-[#eee]!">
                                <td class="py-2.5! text-[#555]!">Petitions :</td>
                                <td class="py-2.5! text-right! font-medium!">
                                    {{ $petitionCount }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2.5! text-[#555]!">Signatures :</td>
                                <td class="py-2.5! text-right! font-medium!">
                                    {{ $signatureCount }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-8">

                        <div class="mb-4">
                            <h2 class="text-base! font-bold! mb-3!">
                                Recent petitions of {{ $user->display_name }}
                            </h2>

                            @forelse ($recentPetitions as $p)
                                <div class="py-1.5! border-b! border-[#f2f2f2]! text-sm!">
                                    <a class="text-brand!" href="{{ route('petition.show', ['slug' => $p->slug, 'id' => $p->id]) }}">
                                        {{ $p->title }}
                                    </a>
                                </div>
                            @empty
                                <div class="text-[13px]! text-[#999]!">No petitions yet.</div>
                            @endforelse
                        </div>

                        <div>
                            <h2 class="text-base! font-bold! mb-3!">
                                Recent signatures of {{ $user->display_name }}
                            </h2>

                            @forelse ($recentSignatures as $s)
                                <div class="py-1.5! border-b! border-[#f2f2f2]! text-sm!">
                                    <a class="text-brand!" href="{{ route('petition.show', ['slug' => $s->slug, 'id' => $s->petition_id]) }}">
                                        {{ $s->title }}
                                    </a>
                                </div>
                            @empty
                                <div class="text-[13px]! text-[#999]!">No signatures yet.</div>
                            @endforelse
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection
