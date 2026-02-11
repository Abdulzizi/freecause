@php
    if (!method_exists($p, 'currentPage')) {
        return;
    }

    $current = $p->currentPage();
    $last = method_exists($p, 'lastPage') ? $p->lastPage() : $current;

    $window = 5;
    $start = max(1, $current - $window);
    $end = min($last, $current + $window);
@endphp

<div style="display:flex; gap:4px; align-items:center; font-size:11px;">
    @if($current > 1)
        <a href="{{ $p->url($current - 1) }}"
            style="padding:2px 6px; border:1px solid #ccc; background:#f7f7f7; text-decoration:none; color:#000;">
            «
        </a>
    @endif

    @if($start > 1)
        <a href="{{ $p->url(1) }}"
            style="padding:2px 6px; border:1px solid #ccc; background:#f7f7f7; text-decoration:none; color:#000;">
            1
        </a>

        @if($start > 2)
            <span style="padding:0 4px;">…</span>
        @endif
    @endif

    @for($i = $start; $i <= $end; $i++)
        @if($i === $current)
            <span style="min-width:22px; text-align:center; padding:2px 6px;
                        border:1px solid #ccc; background:#ddd;">
                {{ $i }}
            </span>
        @else
            <a href="{{ $p->url($i) }}"
            style="min-width:22px; text-align:center; padding:2px 6px;
                    border:1px solid #ccc; background:#f7f7f7;
                    text-decoration:none; color:#000;">
                {{ $i }}
            </a>
        @endif
    @endfor

    @if($end < $last)
        @if($end < $last - 1)
            <span style="padding:0 4px;">…</span>
        @endif

        <a href="{{ $p->url($last) }}"
            style="padding:2px 6px; border:1px solid #ccc; background:#f7f7f7; text-decoration:none; color:#000;">
            {{ $last }}
        </a>
    @endif

    @if($current < $last)
        <a href="{{ $p->url($current + 1) }}"
            style="padding:2px 6px; border:1px solid #ccc; background:#f7f7f7; text-decoration:none; color:#000;">
            »
        </a>
    @endif

</div>
