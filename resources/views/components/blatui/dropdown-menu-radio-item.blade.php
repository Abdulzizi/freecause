@props([
    'value' => '',
    'closeOnSelect' => false,
])

<x-blatui.menu-radio-item
    :data-slot="'dropdown-menu-radio-item'"
    :value="$value"
    :close-on-select="$closeOnSelect"
    {{ $attributes }}
>{{ $slot }}</x-blatui.menu-radio-item>
