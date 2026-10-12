@props([
    'href' => null,
    'variant' => 'default',
    'inset' => false,
    'disabled' => false,
    'closeOnSelect' => true,
    'type' => 'button',
])

<x-blatui.menu-item
    :data-slot="'dropdown-menu-item'"
    :href="$href"
    :variant="$variant"
    :inset="$inset"
    :disabled="$disabled"
    :close-on-select="$closeOnSelect"
    :type="$type"
    {{ $attributes }}
>{{ $slot }}</x-blatui.menu-item>
