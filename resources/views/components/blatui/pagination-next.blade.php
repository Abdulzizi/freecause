@props([
    'href' => '#',
    'label' => null,
    'ariaLabel' => null,
])

@php
    $label ??= __('Next');
    $ariaLabel ??= __('Go to next page');
@endphp

<x-blatui.pagination-link
    :href="$href"
    size="default"
    :aria-label="$ariaLabel"
    {{ $attributes->twMerge('gap-1 px-2.5 sm:pe-2.5') }}
>
    <span class="hidden sm:block">{{ $label }}</span>
    <x-lucide-chevron-right class="rtl:rotate-180" />
</x-blatui.pagination-link>
