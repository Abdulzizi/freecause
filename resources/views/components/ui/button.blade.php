@props([
    'variant' => 'primary',
    'type' => 'submit',
    'size' => 'md',
    'href' => null,
    'loading' => false,
    'loadingText' => null,
])

@php
    $variantClass = match ($variant) {
        'secondary' => 'fc-btn--secondary',
        'danger' => 'fc-btn--danger',
        'ghost' => 'fc-btn--ghost',
        default => 'fc-btn--primary',
    };

    $sizeClass = match ($size) {
        'sm' => 'fc-btn--sm',
        'lg' => 'fc-btn--lg',
        default => 'fc-btn--md',
    };

    $busyText = $loadingText ?? __('ui.working');
    $classes = trim("fc-btn {$variantClass} {$sizeClass}");
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => $classes]) }}
        data-loading
        data-loading-text="{{ $busyText }}"
    >
        <x-ui.spinner class="fc-btn__spinner" />
        <span class="fc-btn__label">{{ $slot }}</span>
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($loading)
        @if ($loading) aria-busy="true" @endif
        {{ $attributes->merge(['class' => $classes]) }}
        data-loading
        data-loading-text="{{ $busyText }}"
    >
        <x-ui.spinner class="fc-btn__spinner" />
        <span class="fc-btn__label">{{ $slot }}</span>
    </button>
@endif
