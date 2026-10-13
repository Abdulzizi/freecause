@props([
    'id',
    'title' => null,
    'variant' => 'bottom',
    'closeLabel' => null,
])

@php
    $classes = 'fcui-sheet'.($variant === 'center' ? ' fcui-sheet--center' : '');
@endphp

<dialog
    id="{{ $id }}"
    class="{{ $classes }}"
    data-sheet
    aria-labelledby="{{ $id }}-title"
>
    <div class="fcui-sheet__grip" data-sheet-grip aria-hidden="true"></div>

    <div class="fcui-sheet__head">
        <span id="{{ $id }}-title">{{ $title ?? 'FreeCause' }}</span>
        <button type="button" class="fcui-sheet__close" data-sheet-close aria-label="{{ $closeLabel ?? __('common.close') }}">
            <x-lucide-x aria-hidden="true" />
        </button>
    </div>

    <div class="fcui-sheet__body">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="fcui-sheet__foot">{{ $footer }}</div>
    @endisset
</dialog>
