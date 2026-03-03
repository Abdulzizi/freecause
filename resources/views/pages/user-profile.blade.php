@extends('layouts.legacy')

@section('title', $user->display_name . ' - FreeCause')

@section('content')

    <section class="py-5">
        <div class="container">

            <div class="mb-4">
                <h1 class="mb-2" style="font-size:24px; font-weight:600;">
                    {{ $user->display_name }}
                </h1>
                <div style="font-size:14px;">
                    <a class="red" href="/{{ app()->getLocale() }}">Home</a>
                    <span class="text-muted"> / </span>
                    <span class="text-muted">{{ $user->display_name }}</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4" style="border:1px solid #eee;">

                <div class="mb-4 headings" style="font-weight:700;">{{ $user->display_name }}</div>

                <div class="row g-4">

                    <div class="col-md-4">
                        <table style="width:100%; font-size:14px; border-collapse:collapse;">
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:10px 0; color:#555;">Registration date :</td>
                                <td style="padding:10px 0; text-align:right; font-weight:500;">
                                    {{ optional($user->created_at)->format('n/j/y H:i') }}
                                </td>
                            </tr>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:10px 0; color:#555;">Petitions :</td>
                                <td style="padding:10px 0; text-align:right; font-weight:500;">
                                    {{ $petitionCount }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:10px 0; color:#555;">Signatures :</td>
                                <td style="padding:10px 0; text-align:right; font-weight:500;">
                                    {{ $signatureCount }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-8">

                        <div class="mb-4">
                            <h2 style="font-size:16px; font-weight:700; margin-bottom:12px;">
                                Recent petitions of {{ $user->display_name }}
                            </h2>

                            @forelse ($recentPetitions as $p)
                                <div style="padding:6px 0; border-bottom:1px solid #f2f2f2; font-size:14px;">
                                    <a class="red" href="{{ route('petition.show', ['slug' => $p->slug, 'id' => $p->id]) }}">
                                        {{ $p->title }}
                                    </a>
                                </div>
                            @empty
                                <div style="font-size:13px; color:#999;">No petitions yet.</div>
                            @endforelse
                        </div>

                        <div>
                            <h2 style="font-size:16px; font-weight:700; margin-bottom:12px;">
                                Recent signatures of {{ $user->display_name }}
                            </h2>

                            @forelse ($recentSignatures as $s)
                                <div style="padding:6px 0; border-bottom:1px solid #f2f2f2; font-size:14px;">
                                    <a class="red" href="{{ route('petition.show', ['slug' => $s->slug, 'id' => $s->petition_id]) }}">
                                        {{ $s->title }}
                                    </a>
                                </div>
                            @empty
                                <div style="font-size:13px; color:#999;">No signatures yet.</div>
                            @endforelse
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection
