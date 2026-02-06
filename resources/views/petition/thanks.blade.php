@extends('layouts.legacy')

@php
    $content = $content ?? collect();

    $isCreated = (($mode ?? 'signed') === 'created');

    $pageTitle = $isCreated
        ? ($content['title_created'] ?? 'Thanks! - FreeCause')
        : ($content['title_signed'] ?? 'Thank you for having signed - FreeCause');

    $h1Text = $isCreated
        ? ($content['h1_created'] ?? 'Thanks!')
        : ($content['h1_signed'] ?? 'Thank you for having signed:');

    $pText = $isCreated
        ? ($content['p_created'] ?? 'Your petition has been created successfully. You can open it now using the link above.')
        : ($content['p_signed'] ?? 'Registration has been successful, however you still have to activate your account by clicking a link you\'ll receive soon at the supplied email address.');

    $suggestionsTitle = $content['suggestions_h2'] ?? 'Petitions you might like';
    $suggestionsEmpty = $content['suggestions_empty'] ?? 'No suggestions yet.';
    $inviteBtnText = $content['invite_btn'] ?? 'Invite friends from your address book »';

    $petitionUrl = isset($tr) && $tr
        ? lroute('petition.show', ['slug' => $tr->slug, 'id' => $petition->id])
        : '#';
@endphp

@section('title', $pageTitle)

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="bg-white shadow-sm rounded-3 p-4" style="border:1px solid #eee; max-width: 820px; margin: 0 auto;">

                <h1 class="mb-3" style="font-size:28px;">
                    {{ $h1Text }}
                </h1>

                <div class="mb-3">
                    <a class="red" href="{{ $petitionUrl }}">
                        {{ $tr->title ?? ($content['petition_fallback'] ?? 'petition') }}
                    </a>
                </div>

                <p class="mb-4" style="font-size:15px;">
                    {{ $pText }}
                </p>

                <h2 class="mb-3" style="font-size:22px;">
                    {{ $suggestionsTitle }}
                </h2>

                <div class="mb-4">
                    @forelse($suggestions as $p)
                        @php
                            $sUrl = !empty($p->tr_slug)
                                ? lroute('petition.show', ['slug' => $p->tr_slug, 'id' => $p->id])
                                : '#';
                        @endphp

                        <div class="mb-2">
                            <a class="d-block p-2" style="border:1px solid #f0caca; border-radius:4px; background:#fff6f6;"
                                href="{{ $sUrl }}">
                                {{ $p->tr_title ?? ($content['petition_fallback'] ?? 'petition') }}
                            </a>
                        </div>
                    @empty
                        <div class="text-muted" style="font-size:14px;">
                            {{ $suggestionsEmpty }}
                        </div>
                    @endforelse
                </div>

                <a class="btn btn-danger" href="#">
                    {{ $inviteBtnText }}
                </a>
            </div>
        </div>
    </section>
@endsection
