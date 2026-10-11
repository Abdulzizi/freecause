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

<div class="fc-box mt-2.5! p-2.5!">
    <div class="flex! items-center! justify-between! mb-2.5!">
        @if (!empty($p) && $p->hasPages())
            @include('admin.partials.simple-window-pagination', ['p' => $p])
        @endif
    </div>

    @if ($isEmpty)
        <div class="px-2.5! py-3! text-muted! text-[12px]!">
            {{ $emptyText }}
            @if ($emptyClearRoute)
                <a href="{{ $emptyClearRoute }}" class="fc-abtn fc-abtn--neutral ml-1.5!">clear filters</a>
            @endif
        </div>
    @else
        <div class="fc-table-wrap">
            <table class="w-full! border-collapse!">
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
    .fc-box table {
        font-size: 13px;
        line-height: 1.45;
    }

    .fc-box table th,
    .fc-box table td {
        padding: 8px 10px;
        vertical-align: middle;
        text-align: left;
    }

    .fc-box table thead th {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--color-muted);
        background: var(--color-surface-muted);
        border-bottom: 1px solid var(--color-border);
        white-space: nowrap;
    }

    .fc-box table tbody td {
        border-bottom: 1px solid var(--color-border);
    }

    .fc-box table tbody tr:last-child td {
        border-bottom: 0;
    }

    .fc-box table tbody tr:hover {
        background: var(--color-surface-muted);
    }

    .fc-box .fc-row-actions-cell {
        width: 1%;
        white-space: nowrap;
        text-align: right;
    }

    .fc-box .fc-table-wrap {
        overflow-x: auto;
    }

    .fc-box .bulk-action:hover,
    .fc-box a[title]:hover {
        background: var(--color-surface-muted) !important;
    }

    .fc-box .bulk-action,
    .fc-box a[title] {
        cursor: pointer;
    }
</style>
