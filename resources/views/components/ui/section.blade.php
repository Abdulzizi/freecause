@props([
    'tone' => null,
    'tight' => false,
    'container' => true,
])

@php
    $classes = 'fcui-section'.($tone === 'band' ? ' fcui-section--band' : '').($tight ? ' fcui-section--tight' : '');
@endphp

<section {{ $attributes->merge(['class' => $classes]) }}>
    @if ($container)
        <x-ui.container>{{ $slot }}</x-ui.container>
    @else
        {{ $slot }}
    @endif
</section>
