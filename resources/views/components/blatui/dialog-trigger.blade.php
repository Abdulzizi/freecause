
@props(['for' => null])

<span
    @if ($for)
        x-data
        @click="$dispatch('open-dialog-{{ $for }}')"
        aria-haspopup="dialog"
    @else
        @click="open = true"
        x-blat-trigger="{ haspopup: 'dialog', controls: $id('blat-dialog') }"
    @endif
    data-slot="dialog-trigger"
    {{ $attributes->twMerge('inline-block') }}
>
    {{ $slot }}
</span>
