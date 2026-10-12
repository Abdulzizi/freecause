
@props(['open' => false, 'id' => null])

<div
    data-slot="alert-dialog"
    x-data="{ open: @js((bool) $open) }"
    @if ($id)
        @open-alert-dialog-{{ $id }}.window="open = true"
        @close-alert-dialog-{{ $id }}.window="open = false"
    @endif
    x-id="['blat-alert-dialog']"
    {{ $attributes }}
>
    {{ $slot }}
</div>
