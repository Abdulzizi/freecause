
@props([
    'title',
    'tabClass' => '',
    'boxClass' => '',
])

<div {{ $attributes->class(['flex items-center gap-2 mb-2 font-semibold text-sm uppercase tracking-wider text-ink', $tabClass]) }}>
    {{ $title }}
</div>

<x-blatui.card class="p-4 mb-5 {{ $boxClass }}">
    {{ $slot }}
</x-blatui.card>
