@php
    if (!method_exists($p, 'currentPage')) {
        return;
    }

    $current  = $p->currentPage();
    $hasMore  = method_exists($p, 'hasMorePages') ? $p->hasMorePages() : ($current < ($p->lastPage() ?? $current));
    $lastPage = method_exists($p, 'lastPage') ? $p->lastPage() : null;
    $window   = 5;
    $start    = max(1, $current - $window);
    $end      = $lastPage ? min($lastPage, $current + $window) : $current;
    $perPage  = method_exists($p, 'perPage') ? $p->perPage() : 25;
    $total    = method_exists($p, 'total') ? $p->total() : null;
    $from     = $total ? (($current - 1) * $perPage) + 1 : null;
    $to       = $total ? min($from + $perPage - 1, $total) : null;
@endphp

<div class="flex! items-center! justify-between! flex-wrap! gap-2!">
    <x-blatui.pagination class="mx-0! w-auto! justify-start!">
        <x-blatui.pagination-content>
            @if ($current > 1)
                <x-blatui.pagination-item>
                    <x-blatui.pagination-link :href="$p->url($current - 1)" size="icon" aria-label="Previous page">
                        <x-lucide-chevron-left class="size-4" aria-hidden="true" />
                    </x-blatui.pagination-link>
                </x-blatui.pagination-item>
            @endif

            @if ($lastPage)
                @if ($start > 1)
                    <x-blatui.pagination-item>
                        <x-blatui.pagination-link :href="$p->url(1)" size="icon" aria-label="Page 1">1</x-blatui.pagination-link>
                    </x-blatui.pagination-item>
                    @if ($start > 2)
                        <x-blatui.pagination-item><x-blatui.pagination-ellipsis /></x-blatui.pagination-item>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    <x-blatui.pagination-item>
                        <x-blatui.pagination-link :href="$p->url($i)" size="icon" :is-active="$i === $current" :aria-label="'Page '.$i">{{ $i }}</x-blatui.pagination-link>
                    </x-blatui.pagination-item>
                @endfor

                @if ($end < $lastPage)
                    @if ($end < $lastPage - 1)
                        <x-blatui.pagination-item><x-blatui.pagination-ellipsis /></x-blatui.pagination-item>
                    @endif
                    <x-blatui.pagination-item>
                        <x-blatui.pagination-link :href="$p->url($lastPage)" size="icon" :aria-label="'Page '.$lastPage">{{ $lastPage }}</x-blatui.pagination-link>
                    </x-blatui.pagination-item>
                @endif
            @else
                <x-blatui.pagination-item>
                    <x-blatui.pagination-link :href="$p->url($current)" size="icon" :is-active="true" :aria-label="'Page '.$current">{{ $current }}</x-blatui.pagination-link>
                </x-blatui.pagination-item>
            @endif

            @if ($hasMore)
                <x-blatui.pagination-item>
                    <x-blatui.pagination-link :href="$p->url($current + 1)" size="icon" aria-label="Next page">
                        <x-lucide-chevron-right class="size-4" aria-hidden="true" />
                    </x-blatui.pagination-link>
                </x-blatui.pagination-item>
            @endif
        </x-blatui.pagination-content>
    </x-blatui.pagination>

    @if ($total !== null)
        <span class="text-[11px]! text-muted! whitespace-nowrap!">Showing {{ number_format($from) }}–{{ number_format($to) }} of {{ number_format($total) }}</span>
    @endif
</div>
