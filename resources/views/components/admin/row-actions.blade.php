
@props(['align' => 'end'])

<x-blatui.dropdown-menu {{ $attributes }}>
    <x-blatui.dropdown-menu-trigger>
        <x-blatui.button variant="outline" size="icon-sm" type="button" aria-label="Row actions" title="Actions">
            <x-lucide-ellipsis class="size-4" aria-hidden="true" />
        </x-blatui.button>
    </x-blatui.dropdown-menu-trigger>
    <x-blatui.dropdown-menu-content align="{{ $align }}" class="w-44!">
        {{ $slot }}
    </x-blatui.dropdown-menu-content>
</x-blatui.dropdown-menu>
