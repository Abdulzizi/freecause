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
        'secondary' => 'fcui-btn--secondary',
        'danger' => 'fcui-btn--danger',
        'ghost' => 'fcui-btn--ghost',
        default => 'fcui-btn--primary',
    };

    $sizeClass = match ($size) {
        'sm' => 'fcui-btn--sm',
        'lg' => 'fcui-btn--lg',
        default => 'fcui-btn--md',
    };

    $busyText = $loadingText ?? __('ui.working');
    $classes = trim("fcui-btn {$variantClass} {$sizeClass}");
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        <span class="fcui-btn__label">{{ $slot }}</span>
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
        <x-ui.spinner class="fcui-btn__spinner" />
        <span class="fcui-btn__label">{{ $slot }}</span>
    </button>
@endif
