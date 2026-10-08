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
