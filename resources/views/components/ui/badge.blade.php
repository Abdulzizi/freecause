@props(['variant' => 'neutral'])

@php
    $variantClass = in_array($variant, ['brand', 'success', 'warning', 'danger', 'info'], true) ? " fcui-badge--{$variant}" : '';
@endphp

<span {{ $attributes->merge(['class' => "fcui-badge{$variantClass}"]) }}>{{ $slot }}</span>
