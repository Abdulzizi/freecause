
@props(['open' => false, 'id' => null])

<div
    data-slot="dialog"
    x-data="{ open: @js((bool) $open) }"
    @if ($id)
        @open-dialog-{{ $id }}.window="open = true"
        @close-dialog-{{ $id }}.window="open = false"
    @endif
    x-id="['blat-dialog']"
    {{ $attributes }}
>
    {{ $slot }}
</div>
