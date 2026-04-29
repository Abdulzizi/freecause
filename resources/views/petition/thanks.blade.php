@extends('layouts.legacy')

@php
    $isCreated = ($mode ?? 'signed') === 'created';

    $pageTitle = $isCreated
        ? __('messages.thanks.title_created')
        : __('messages.thanks.title_signed');

    $h1Text = $isCreated
        ? __('messages.thanks.h1_created')
        : __('messages.thanks.h1_signed');

    $pText = $isCreated
        ? __('messages.thanks.p_created')
        : __('messages.thanks.p_signed');

    $suggestionsTitle = __('messages.thanks.suggestions');
    $suggestionsEmpty = __('messages.thanks.no_suggestions');
    $inviteBtnText = __('messages.thanks.invite');

    $petitionUrl = isset($tr) && $tr ? lroute('petition.show', ['slug' => $tr->slug, 'id' => $petition->id]) : '#';
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
                        {{ $tr->title ?? __('messages.thanks.petition_fallback') }}
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
                                {{ $p->tr_title ?? __('messages.thanks.petition_fallback') }}
                            </a>
                        </div>
                    @empty
                        <div class="text-muted" style="font-size:14px;">
                            {{ $suggestionsEmpty }}
                        </div>
                    @endforelse
                </div>

                <a class="btn btn-danger"
                    href="{{ lroute('petition.show', ['slug' => $tr->slug, 'id' => $petition->id]) }}">
                    {{ $inviteBtnText }}
                </a>
            </div>
        </div>
    </section>
@endsection
