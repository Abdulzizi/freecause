@extends('layouts.legacy')

@php
    $petitionTitle = e($tr->title ?? 'Petition');
    $shortDesc = \Illuminate\Support\Str::limit(strip_tags($tr->description ?? ''), 200);
    $petitionUrl = lroute('petition.show', ['slug' => $tr->slug, 'id' => $petition->id]);
    $signUrl = lroute('petition.sign.page', ['slug' => $tr->slug, 'id' => $petition->id]);
    $coverImg = $petition->coverUrl();
@endphp

@section('title', 'Signatures: ' . $petitionTitle)

@section('content')
    <section class="py-5">
        <div class="container">

            <div class="bg-white shadow-sm rounded-3 p-4 mb-4" style="border:1px solid #eee;">
                <div class="row align-items-center g-4">

                    <div class="col-md-3 col-sm-4">
                        <a href="{{ $petitionUrl }}" class="d-block" style="border-radius:8px;overflow:hidden;display:block;height:160px;background:#1a1a1a;position:relative;">
                            <img src="{{ $coverImg }}" alt="" aria-hidden="true"
                                style="position:absolute;inset:-10px;width:calc(100% + 20px);height:calc(100% + 20px);object-fit:cover;filter:blur(14px);opacity:0.55;">
                            <img src="{{ $coverImg }}" alt="{{ $petitionTitle }}"
                                style="position:relative;z-index:1;width:100%;height:160px;object-fit:contain;">
                        </a>
                    </div>

                    <div class="col-md-6 col-sm-8">
                        <h1 class="mb-2" style="font-size:19px;font-weight:700;line-height:1.35;">
                            <a href="{{ $petitionUrl }}" class="text-dark text-decoration-none">
                                {{ $petitionTitle }}
                            </a>
                        </h1>

                        <p class="text-muted mb-3" style="font-size:14px;line-height:1.6;">
                            {{ $shortDesc }}
                            <a href="{{ $petitionUrl }}" class="red ms-1">{{ __('messages.sig.continue') }}</a>
                        </p>

                        <div class="fc-progress mb-2">
                            <div class="fc-progress-bar" style="width: {{ $pct }}%;"></div>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size:13px;color:#666;">
                            <span><strong>{{ number_format($goalCurrent) }}</strong> {{ __('messages.sig.signatures') }}</span>
                            <span>{{ __('messages.sig.goal') }} {{ number_format($goalTotal) }}</span>
                        </div>
                    </div>

                    <div class="col-md-3 text-md-end">
                        <a href="{{ $signUrl }}" class="btn btn-danger px-4 py-2" style="font-size:15px;">
                            {{ __('messages.sig.sign_link') }}
                        </a>
                    </div>

                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3 p-4" style="border:1px solid #eee;">

                <div class="fc-box-title mb-3">
                    {{ __('messages.sig.all_signatures') }}
                    <span class="text-muted fw-normal" style="font-size:14px;">
                        ({{ number_format($goalCurrent) }} {{ __('messages.sig.total') }})
                    </span>
                </div>

                <div style="height:2px;background:#e9ecef;position:relative;margin-bottom:22px;">
                    <div
                        style="height:2px;width:100%;background:linear-gradient(to right,black,red);position:absolute;left:0;top:0;">
                    </div>
                </div>

                @forelse($signatures as $sig)
                    <div class="d-flex align-items-start gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="flex-shrink-0 text-muted" style="font-size:13px;min-width:32px;padding-top:2px;">
                            {{ ($signatures->currentPage() - 1) * $signatures->perPage() + $loop->iteration }}.
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <strong style="font-size:15px;">{{ $sig->name ?? __('messages.sig.anonymous') }}</strong>
                                @if ($sig->user?->city)
                                    <span class="text-muted" style="font-size:13px;">
                                        · {{ $sig->user->city }}
                                    </span>
                                @endif
                                <span class="text-muted ms-auto" style="font-size:12px;">
                                    {{ optional($sig->created_at)->format('j F Y') }}
                                </span>
                            </div>
                            @if (!empty($sig->text) && $sig->text !== 'I support this petition')
                                <p class="mb-0 mt-1 text-muted" style="font-size:14px;">
                                    "{{ $sig->text }}"
                                </p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-muted py-4 text-center" style="font-size:14px;">
                        {{ __('messages.sig.no_signatures') }}
                    </div>
                @endforelse

                @include('partials.pagination', ['paginator' => $signatures])

            </div>

        </div>
    </section>
@endsection
