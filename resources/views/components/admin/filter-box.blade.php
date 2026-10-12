@props([
    'title' => 'filter',
    'action' => '',
    'reset' => '',
    'activeCount' => 0,
])

<div class="flex! items-center! gap-2! mb-2!">
    <span class="font-semibold! text-[13px]! uppercase! tracking-[0.05em]! text-ink!">{{ $title }}</span>
    @if ($activeCount > 0)
        <x-blatui.badge tone="info">{{ $activeCount }} active</x-blatui.badge>
    @endif
</div>

<x-blatui.card class="p-3! mb-3!">
    <form method="get" action="{{ $action }}" class="fc-filter-form" data-live-search>
        {{ $slot }}

        <div class="fc-filter-actions">
            <x-blatui.button type="submit" size="sm">apply</x-blatui.button>
            <x-blatui.button variant="outline" size="sm" :href="$reset">reset</x-blatui.button>
        </div>
    </form>
</x-blatui.card>
