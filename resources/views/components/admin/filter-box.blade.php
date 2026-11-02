@props([
    'title' => 'filter',
    'action' => '',
    'reset' => '',
    'activeCount' => 0,
])

@php
    $fields = preg_replace_callback(
        '/<(select|input)\b([^>]*)>/i',
        fn ($m) => preg_match('/(?<![\w:-])aria-label\b|(?<![\w:-])type\s*=\s*["\']?(?:checkbox|radio|hidden)\b/i', $m[2]) || ! preg_match('/(?<![\w:-])name\s*=\s*["\']([^"\']+)["\']/i', $m[2], $n)
            ? $m[0]
            : '<' . $m[1] . ' aria-label="' . (preg_match('/(?<![\w:-])placeholder\s*=\s*["\']([^"\']+)["\']/i', $m[2], $p) ? $p[1] : e(str_replace('_', ' ', $n[1]))) . '"' . $m[2] . '>',
        (string) $slot
    ) ?? (string) $slot;
@endphp

<div class="flex items-center gap-2 mb-2">
    <span class="font-semibold text-sm uppercase tracking-wider text-ink">{{ $title }}</span>
    @if ($activeCount > 0)
        <x-blatui.badge tone="info">{{ $activeCount }} active</x-blatui.badge>
    @endif
</div>

<x-blatui.card class="p-3 mb-3">
    <form method="get" action="{{ $action }}" class="fc-filter-form" data-live-search>
        {!! $fields !!}

        <div class="fc-filter-actions">
            <x-blatui.button type="submit" size="sm">apply</x-blatui.button>
            <x-blatui.button variant="outline" size="sm" :href="$reset">reset</x-blatui.button>
        </div>
    </form>
</x-blatui.card>
