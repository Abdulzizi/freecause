@props([
    'variant' => 'text',
    'lines' => 1,
])

@php
    $variant = in_array($variant, ['text', 'title'], true) ? $variant : 'text';
@endphp

@if ($lines > 1)
    <div {{ $attributes->merge(['class' => 'space-y-2']) }}>
        @for ($i = 0; $i < $lines; $i++)
            <span class="fcui-skeleton fcui-skeleton--{{ $variant }}"></span>
        @endfor
    </div>
@else
    <span {{ $attributes->merge(['class' => "fcui-skeleton fcui-skeleton--{$variant}"]) }}></span>
@endif
