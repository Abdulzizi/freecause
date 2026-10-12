
@props(['for' => null])

<span
    @if ($for)
        x-data
        @click="$dispatch('open-alert-dialog-{{ $for }}')"
        aria-haspopup="dialog"
    @else
        @click="open = true"
        x-blat-trigger="{ haspopup: 'dialog', controls: $id('blat-alert-dialog') }"
    @endif
    data-slot="alert-dialog-trigger"
    {{ $attributes->twMerge('inline-block') }}
>
    {{ $slot }}
</span>
