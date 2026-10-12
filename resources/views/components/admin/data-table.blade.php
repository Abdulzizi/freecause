
@props([
    'p' => null,
    'bulk' => null,
    'emptyText' => null,
    'emptyClearRoute' => null,
])

@php($resolvedEmptyText = $emptyText ?? $attributes->get('empty-text') ?? 'no results')

<x-admin.list-table-box :p="$p" :bulk="$bulk" :emptyText="$resolvedEmptyText" :emptyClearRoute="$emptyClearRoute" {{ $attributes->except(['empty-text']) }}>
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
