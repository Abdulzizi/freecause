
@props(['for' => null])

<span
    @if ($for)
        x-data
        @click="$dispatch('open-sheet-{{ $for }}')"
        aria-haspopup="dialog"
    @else
        @click="open = true"
        x-blat-trigger="{ haspopup: 'dialog', controls: $id('blat-sheet') }"
    @endif
    data-slot="sheet-trigger"
    {{ $attributes->twMerge('inline-block') }}
>
    {{ $slot }}
</span>
