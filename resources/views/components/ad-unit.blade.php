@props([
    'placement' => null,
    'slotId' => null,
    'format' => 'auto',
    'responsive' => true,
    'minHeight' => '90px',
])

@php
    $adsenseEnabled = (bool) config('services.adsense.enabled');
    $client = config('services.adsense.client');
    $resolvedSlot = $slotId ?? ($placement ? config('services.adsense.slots.'.$placement) : null);
@endphp

@if ($adsenseEnabled && $client && $resolvedSlot)
    <div {{ $attributes->merge(['class' => 'fcui-ad w-full overflow-hidden text-center']) }} aria-hidden="true">
        <ins class="adsbygoogle block"
            style="display:block;min-height:{{ $minHeight }}"
            data-adsense="1"
            data-ad-client="{{ $client }}"
            data-ad-slot="{{ $resolvedSlot }}"
            data-ad-format="{{ $format }}"
            @if ($responsive) data-full-width-responsive="true" @endif></ins>
    </div>
@endif
