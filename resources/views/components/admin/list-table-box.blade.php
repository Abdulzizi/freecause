@props([
    'p' => null,
    'bulk' => null,
    'emptyText' => 'no results',
])

@php
    $hasFooter = isset($footer) && trim((string) $footer) !== '';
    $isEmpty = empty($p) ? true : $p->total() === 0;
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
        </div>
    @else
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                {{ $thead }}
            </thead>
            <tbody>
                {{ $tbody }}
            </tbody>
        </table>

        @if ($bulk !== null || $hasFooter)
            <div
                style="font-size:11px; color:#555; display:flex; justify-content:space-between; align-items:flex-end;">
                <div>
                    @if ($bulk != null)
                        @include('admin.partials.bulk-toolbar', $bulk)
                    @endif
                </div>

                <div style="display:flex; gap:10px; align-items:flex-end;">
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
