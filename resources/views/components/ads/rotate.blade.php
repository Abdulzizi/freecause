@props(['placement' => 'inline'])

{{-- Picks the next unrendered slot from a placement pool and renders it.
     Being a component keeps page views free of PHP blocks: Blade pairs the
     first block-opening PHP directive with the first closing one, so an
     inline PHP assignment earlier in the same view would swallow the block. --}}
@php
    $slotName = \App\Support\Ads::pick($placement);
@endphp

@if ($slotName)
    <x-ads.slot :name="$slotName" {{ $attributes }} />
@endif