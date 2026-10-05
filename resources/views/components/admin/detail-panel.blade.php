{{--
    Right-side slide-over drawer for all admin editing detail panels
    (petitions, users, pages, categories). Opens automatically when the list
    page carries a ?select= param; ESC or the close link exits.
--}}
@props([
    'title' => 'detail',
    'closeRoute' => null,
    'drawerId' => 'fc-detail-drawer',
])

@php
    $open = filled(request()->query('select')) ? '1' : '0';
@endphp

<div class="fc-drawer-backdrop" data-open="{{ $open }}"></div>

<aside class="fc-drawer" id="{{ $drawerId }}" data-open="{{ $open }}">
    <div class="fc-drawer__head">
        <span>{{ $title }}</span>
        @if ($closeRoute)
            <a href="{{ $closeRoute }}" class="fc-drawer__close" title="Close panel" aria-label="Close panel">&times;</a>
        @endif
    </div>
    <div class="fc-drawer__body">
        {{ $slot }}
    </div>
</aside>
