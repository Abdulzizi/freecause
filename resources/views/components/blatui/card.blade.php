@props(['variant' => 'default'])

@php
    $variants = [
        'default' => 'bg-card text-card-foreground rounded-xl border p-6 shadow-sm',
        'sectioned' => 'bg-card text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm',
    ];

    $classes = $variants[$variant] ?? $variants['default'];
@endphp

<div data-slot="card" {{ $attributes->twMerge($classes) }}>
    {{ $slot }}
</div>
