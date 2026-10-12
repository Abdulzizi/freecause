@props([
    'dataSlot' => 'menu-group',
    'labelSlot' => 'menu-label',
    'compact' => false,
])

@if ($compact)
<div data-slot="{{ $dataSlot }}" role="group" x-blat-labelledby="{ label: ':scope > [data-slot={{ $labelSlot }}]' }" {{ $attributes }}>{{ $slot }}</div>
@else
<div role="group" data-slot="{{ $dataSlot }}" x-blat-labelledby="{ label: ':scope > [data-slot={{ $labelSlot }}]' }" {{ $attributes }}>
    {{ $slot }}
</div>
@endif
