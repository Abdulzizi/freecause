@props([
    'size' => 'md',
])

@php
    $sizeClass = match ($size) {
        'sm' => 'fcui-spinner--sm',
        'lg' => 'fcui-spinner--lg',
        default => 'fcui-spinner--md',
    };
@endphp

<svg
    {{ $attributes->merge(['class' => "fcui-spinner {$sizeClass}"]) }}
    viewBox="0 0 24 24"
    fill="none"
    aria-hidden="true"
    focusable="false"
>
    <circle class="fcui-spinner__track" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" />
    <path class="fcui-spinner__head" d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
</svg>
