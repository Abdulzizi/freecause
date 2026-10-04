@props([
    'size' => 'md',
])

@php
    $sizeClass = match ($size) {
        'sm' => 'fc-spinner--sm',
        'lg' => 'fc-spinner--lg',
        default => 'fc-spinner--md',
    };
@endphp

<svg
    {{ $attributes->merge(['class' => "fc-spinner {$sizeClass}"]) }}
    viewBox="0 0 24 24"
    fill="none"
    aria-hidden="true"
    focusable="false"
>
    <circle class="fc-spinner__track" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" />
    <path class="fc-spinner__head" d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
</svg>
