@if (method_exists($paginator, 'hasPages') && $paginator->hasPages())
    <div class="d-flex gap-1 mt-4 flex-wrap">

        @if ($paginator->onFirstPage())
            <span class="fc-page disabled">«</span>
        @else
            <a class="fc-page" href="{{ $paginator->previousPageUrl() }}">«</a>
        @endif

        {{-- pages --}}
        @php
            $start = max(1, $paginator->currentPage() - 2);
            $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
        @endphp

        @if ($start > 1)
            <a class="fc-page" href="{{ $paginator->url(1) }}">1</a>
            @if ($start > 2)
                <span class="fc-page disabled">...</span>
            @endif
        @endif

        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $paginator->currentPage())
                <span class="fc-page active">{{ $page }}</span>
            @else
                <a class="fc-page" href="{{ $paginator->url($page) }}">{{ $page }}</a>
            @endif
        @endfor

        @if ($end < $paginator->lastPage())
            @if ($end < $paginator->lastPage() - 1)
                <span class="fc-page disabled">...</span>
            @endif
            <a class="fc-page" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a class="fc-page" href="{{ $paginator->nextPageUrl() }}">»</a>
        @else
            <span class="fc-page disabled">»</span>
        @endif

    </div>
@endif
