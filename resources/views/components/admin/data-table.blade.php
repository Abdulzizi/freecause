{{--
    Thin wrapper over x-admin.list-table-box; preserves its table, toolbar, footer, and pagination markup.
    Props: p (optional paginator), bulk (optional bulk toolbar config), emptyText (optional message).
    Blade normalizes both empty-text and emptyText call-site spellings to emptyText.
    Named slots: thead, tbody, footer.
--}}
@props([
    'p' => null,
    'bulk' => null,
    'emptyText' => null,
])

@php($resolvedEmptyText = $emptyText ?? $attributes->get('empty-text') ?? 'no results')

<x-admin.list-table-box :p="$p" :bulk="$bulk" :emptyText="$resolvedEmptyText" {{ $attributes->except(['empty-text']) }}>
    @isset($thead)
        @slot('thead'){{ $thead }}@endslot
    @endisset
    @isset($tbody)
        @slot('tbody'){{ $tbody }}@endslot
    @endisset
    @isset($footer)
        @slot('footer'){{ $footer }}@endslot
    @endisset
    {{ $slot }}
</x-admin.list-table-box>
