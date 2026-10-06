@props([
    'p' => null,
    'bulk' => null,
    'emptyText' => 'no results',
    'emptyClearRoute' => null,
])

@php
    $hasFooter = isset($footer) && trim((string) $footer) !== '';
    $isEmpty = empty($p) ? true : $p->isEmpty();
@endphp

<div class="fc-box" style="margin-top:10px; padding:10px 10px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
        @if (!empty($p) && $p->hasPages())
            @include('admin.partials.simple-window-pagination', ['p' => $p])
        @endif
    </div>

    @if ($isEmpty)
        <div style="padding:12px 10px; color:#777; font-size:12px;">
            {{ $emptyText }}
            @if ($emptyClearRoute)
                <a href="{{ $emptyClearRoute }}" class="fc-abtn fc-abtn--neutral" style="margin-left:6px;">clear filters</a>
            @endif
        </div>
    @else
        <div class="fc-table-wrap">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    {{ $thead }}
                </thead>
                <tbody>
                    {{ $tbody }}
                </tbody>
            </table>
        </div>

        @if ($bulk !== null || $hasFooter)
            <div class="fc-toolbar">
                <div class="fc-toolbar-left">
                    @if ($bulk != null)
                        @include('admin.partials.bulk-toolbar', $bulk)
                    @endif
                </div>

                <div class="fc-toolbar-right">
                    @if ($hasFooter)
                        {{ $footer }}
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>

<style>
    .fc-box table th,
    .fc-box table td {
        padding: 6px 8px;
        vertical-align: middle;
    }

    .fc-box .bulk-action:hover,
    .fc-box a[title]:hover {
        background: #f5f5f5 !important;
    }

    .fc-box .bulk-action,
    .fc-box a[title] {
        cursor: pointer;
    }
</style>
