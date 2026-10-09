@if (method_exists($paginator, 'hasPages') && $paginator->hasPages())
    <div class="flex! gap-1! mt-4! flex-wrap!">

        @if ($paginator->onFirstPage())
            <span class="min-w-[32px]! h-[32px]! rounded-md! border! border-border! inline-flex! items-center! justify-center! text-[13px]! text-muted! bg-[#f1f1f1]! no-underline! pointer-events-none! transition-colors! duration-150! ease-[ease]!">«</span>
        @else
            <a class="min-w-[32px]! h-[32px]! rounded-md! border! border-border! inline-flex! items-center! justify-center! text-[13px]! text-brand! bg-[#f8f9fa]! no-underline! transition-colors! duration-150! ease-[ease]! hover:bg-brand! hover:text-white! hover:border-brand!" href="{{ $paginator->previousPageUrl() }}">«</a>
        @endif

        @php
            $start = max(1, $paginator->currentPage() - 2);
            $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
        @endphp

        @if ($start > 1)
            <a class="min-w-[32px]! h-[32px]! rounded-md! border! border-border! inline-flex! items-center! justify-center! text-[13px]! text-brand! bg-[#f8f9fa]! no-underline! transition-colors! duration-150! ease-[ease]! hover:bg-brand! hover:text-white! hover:border-brand!" href="{{ $paginator->url(1) }}">1</a>
            @if ($start > 2)
                <span class="min-w-[32px]! h-[32px]! rounded-md! border! border-border! inline-flex! items-center! justify-center! text-[13px]! text-muted! bg-[#f1f1f1]! no-underline! pointer-events-none! transition-colors! duration-150! ease-[ease]!">...</span>
            @endif
        @endif

        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $paginator->currentPage())
                <span class="min-w-[32px]! h-[32px]! rounded-md! border! border-brand! inline-flex! items-center! justify-center! text-[13px]! text-white! bg-brand! no-underline! font-semibold! transition-colors! duration-150! ease-[ease]!">{{ $page }}</span>
            @else
                <a class="min-w-[32px]! h-[32px]! rounded-md! border! border-border! inline-flex! items-center! justify-center! text-[13px]! text-brand! bg-[#f8f9fa]! no-underline! transition-colors! duration-150! ease-[ease]! hover:bg-brand! hover:text-white! hover:border-brand!" href="{{ $paginator->url($page) }}">{{ $page }}</a>
            @endif
        @endfor

        @if ($end < $paginator->lastPage())
            @if ($end < $paginator->lastPage() - 1)
                <span class="min-w-[32px]! h-[32px]! rounded-md! border! border-border! inline-flex! items-center! justify-center! text-[13px]! text-muted! bg-[#f1f1f1]! no-underline! pointer-events-none! transition-colors! duration-150! ease-[ease]!">...</span>
            @endif
            <a class="min-w-[32px]! h-[32px]! rounded-md! border! border-border! inline-flex! items-center! justify-center! text-[13px]! text-brand! bg-[#f8f9fa]! no-underline! transition-colors! duration-150! ease-[ease]! hover:bg-brand! hover:text-white! hover:border-brand!" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a class="min-w-[32px]! h-[32px]! rounded-md! border! border-border! inline-flex! items-center! justify-center! text-[13px]! text-brand! bg-[#f8f9fa]! no-underline! transition-colors! duration-150! ease-[ease]! hover:bg-brand! hover:text-white! hover:border-brand!" href="{{ $paginator->nextPageUrl() }}">»</a>
        @else
            <span class="min-w-[32px]! h-[32px]! rounded-md! border! border-border! inline-flex! items-center! justify-center! text-[13px]! text-muted! bg-[#f1f1f1]! no-underline! pointer-events-none! transition-colors! duration-150! ease-[ease]!">»</span>
        @endif

    </div>
@endif
