@props([
    'context' => null,
    'label' => null,
    'href' => null,
])

<div {{ $attributes->merge(['class' => 'fcui-sticky-action']) }} data-sticky-action>
    @if ($context)
        <p class="mb-2 text-center text-sm text-muted fc-tabular">{{ $context }}</p>
    @endif

    @if ($href)
        <x-ui.button variant="danger" :href="$href" class="w-full fcui-tap">{{ $label ?? $slot }}</x-ui.button>
    @else
        <x-ui.button type="submit" variant="primary" class="w-full fcui-tap">{{ $label ?? $slot }}</x-ui.button>
    @endif
</div>
