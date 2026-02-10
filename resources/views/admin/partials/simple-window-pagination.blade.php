@php
    $current = $p->currentPage();
    $start = max(1, $current - 0);
    $end = $start + 10;
@endphp

<div style="display:flex; gap:4px; align-items:center;">
    @for($i = $start; $i <= $end; $i++)
        @php
            $url = $p->url($i);
            $active = $i === $current;
        @endphp
        <a href="{{ $url }}"
            style="display:inline-block; min-width:22px; text-align:center; padding:2px 6px; border:1px solid #ccc; background:{{ $active ? '#ddd' : '#f7f7f7' }}; text-decoration:none; color:#000;">
            {{ $i }}
        </a>
    @endfor

    <span style="padding:0 4px;">…</span>

    @if($p->nextPageUrl())
        <a href="{{ $p->nextPageUrl() }}"
            style="display:inline-block; padding:2px 8px; border:1px solid #ccc; background:#f7f7f7; text-decoration:none; color:#000;">
            success »
        </a>
    @endif
</div>
