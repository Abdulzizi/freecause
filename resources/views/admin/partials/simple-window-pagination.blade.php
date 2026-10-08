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
<div class="flex! gap-1! items-center! text-xs! max-md:gap-1.5!">
    @if($current > 1)
        <a href="{{ $p->url($current - 1) }}"
            class="py-0.5! px-1.5! border! border-border! bg-[#f7f7f7]! no-underline! text-ink! max-md:p-2! max-md:px-2.5! max-md:min-w-[36px]! max-md:text-center! max-md:text-[13px]!">
            «
        </a>
    @endif

    @if($lastPage)
        @if($start > 1)
            <a href="{{ $p->url(1) }}"
                class="py-0.5! px-1.5! border! border-border! bg-[#f7f7f7]! no-underline! text-ink! max-md:p-2! max-md:px-2.5! max-md:min-w-[36px]! max-md:text-center! max-md:text-[13px]!">
                1
            </a>
            @if($start > 2)
                <span class="px-1!">…</span>
            @endif
        @endif

        @for($i = $start; $i <= $end; $i++)
            @if($i === $current)
                <span class="min-w-[22px]! text-center! py-0.5! px-1.5! border! border-border! bg-[#ddd]! max-md:min-w-[36px]! max-md:p-2! max-md:px-2.5! max-md:text-center! max-md:text-[13px]!">
                    {{ $i }}
                </span>
            @else
                <a href="{{ $p->url($i) }}"
                    class="min-w-[22px]! text-center! py-0.5! px-1.5! border! border-border! bg-[#f7f7f7]! no-underline! text-ink! max-md:min-w-[36px]! max-md:p-2! max-md:px-2.5! max-md:text-center! max-md:text-[13px]!">
                    {{ $i }}
                </a>
            @endif
        @endfor

        @if($end < $lastPage)
            @if($end < $lastPage - 1)
                <span class="px-1!">…</span>
            @endif
            <a href="{{ $p->url($lastPage) }}"
                class="py-0.5! px-1.5! border! border-border! bg-[#f7f7f7]! no-underline! text-ink! max-md:p-2! max-md:px-2.5! max-md:min-w-[36px]! max-md:text-center! max-md:text-[13px]!">
                {{ $lastPage }}
            </a>
        @endif
    @else
        {{-- simplePaginate: just show current page number --}}
        <span class="min-w-[22px]! text-center! py-0.5! px-1.5! border! border-border! bg-[#ddd]! max-md:min-w-[36px]! max-md:p-2! max-md:px-2.5! max-md:text-center! max-md:text-[13px]!">
            {{ $current }}
        </span>
    @endif

    @if($hasMore)
        <a href="{{ $p->url($current + 1) }}"
            class="py-0.5! px-1.5! border! border-border! bg-[#f7f7f7]! no-underline! text-ink! max-md:p-2! max-md:px-2.5! max-md:min-w-[36px]! max-md:text-center! max-md:text-[13px]!">
            »
        </a>
    @endif
</div>

@if($total !== null)
    <span class="text-[11px]! text-muted! whitespace-nowrap!">Showing {{ number_format($from) }}–{{ number_format($to) }} of {{ number_format($total) }}</span>
@endif
</div>
