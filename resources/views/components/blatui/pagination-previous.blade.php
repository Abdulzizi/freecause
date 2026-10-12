@props([
    'href' => '#',
    'label' => null,
    'ariaLabel' => null,
])

@php
    $label ??= __('Previous');
    $ariaLabel ??= __('Go to previous page');
@endphp

<x-blatui.pagination-link
    :href="$href"
    size="default"
    :aria-label="$ariaLabel"
    {{ $attributes->twMerge('gap-1 px-2.5 sm:ps-2.5') }}
>
    <x-lucide-chevron-left class="rtl:rotate-180" />
    <span class="hidden sm:block">{{ $label }}</span>
</x-blatui.pagination-link>
