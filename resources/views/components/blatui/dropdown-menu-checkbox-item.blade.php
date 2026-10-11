@props([
    'checked' => false,
    'disabled' => false,
    'closeOnSelect' => false,
])

<x-blatui.menu-checkbox-item
    :data-slot="'dropdown-menu-checkbox-item'"
    :checked="$checked"
    :disabled="$disabled"
    :close-on-select="$closeOnSelect"
    {{ $attributes }}
>{{ $slot }}</x-blatui.menu-checkbox-item>
