@props([
    'dataSlot' => 'menu-label',
    'classes' => 'px-2 py-1.5 text-sm font-medium data-[inset]:ps-8',
    'inset' => false,
])

<div
    data-slot="{{ $dataSlot }}"
    role="presentation"
    @if ($inset) data-inset @endif
    {{ $attributes->twMerge($classes) }}
>{{ $slot }}</div>
