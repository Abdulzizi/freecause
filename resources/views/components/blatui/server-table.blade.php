@props([
    'columns' => [],
    'rows' => [],
    'rowKey' => 'id',

    'sort' => null,
    'direction' => 'asc',
    'sortMethod' => 'sortBy',

    'actions' => [],
    'actionsView' => null,
    'actionsLabel' => null,
    'actionsMode' => 'inline',
    'stickyActions' => false,

    'cellViews' => [],

    'selectable' => false,
    'selectModel' => 'selected',

    'searchable' => false,
    'searchModel' => 'search',
    'searchPlaceholder' => null,

    'perPageModel' => 'perPage',
    'perPageOptions' => [],
    'perPageLabel' => null,

    'toggleableColumns' => false,
    'visibleColumns' => null,
    'toggleColumnMethod' => 'toggleColumn',
    'columnsLabel' => null,

    'caption' => null,
    'captionVisible' => false,
    'emptyText' => null,
    'emptyIcon' => 'search-x',
    'responsive' => 'scroll',
    'variant' => 'default',
    'paginate' => true,
])

@php
    use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;

    $actionsLabel ??= __('Actions');
    $searchPlaceholder ??= __('Search...');
    $perPageLabel ??= __('Rows per page');
    $columnsLabel ??= __('Columns');
    $emptyText ??= __('No results.');

    $allCols = collect($columns)->map(fn ($c) => [
        'key' => $c['key'] ?? '',
        'label' => $c['label'] ?? \Illuminate\Support\Str::headline($c['key'] ?? ''),
        'sortable' => $c['sortable'] ?? false,
        'align' => $c['align'] ?? 'left',
        'class' => $c['class'] ?? '',
        'width' => $c['width'] ?? null,
        'hideable' => $c['hideable'] ?? true,
    ])->values();

    $cols = $visibleColumns === null
        ? $allCols
        : $allCols->filter(fn ($c) => ! $c['hideable'] || in_array($c['key'], $visibleColumns, true))->values();

    $isPaginator = $rows instanceof PaginatorContract;
    $items = $isPaginator ? $rows->items() : (is_array($rows) ? $rows : (is_object($rows) && method_exists($rows, 'all') ? $rows->all() : (array) $rows));

    $hasActions = ! empty($actions) || $actionsView !== null;
    $colspan = $cols->count() + ($selectable ? 1 : 0) + ($hasActions ? 1 : 0);

    $alignClass = fn (string $a) => match ($a) {
        'center' => 'text-center',
        'right', 'end' => 'text-end',
        default => 'text-start',
    };

    $actionArgs = function (array $action, $row) use ($rowKey) {
        $params = $action['params'] ?? [data_get($row, $rowKey)];

        return collect($params)->map(function ($p) {
            if (is_bool($p)) return $p ? 'true' : 'false';
            if (is_int($p) || is_float($p) || (is_numeric($p) && ! is_string($p))) return (string) $p;

            return "'".addslashes((string) $p)."'";
        })->implode(', ');
    };

    $actionHref = function (array $action, $row) use ($rowKey) {
        $h = $action['href'] ?? null;
        if ($h instanceof \Closure) return $h($row);
        if (is_string($h)) return str_replace('{id}', (string) data_get($row, $rowKey), $h);

        return null;
    };

    $stack = $responsive === 'stack';

    $wrapperClass = match ($variant) {
        'card' => $stack
            ? 'max-md:border-0 max-md:bg-transparent max-md:shadow-none md:bg-card md:rounded-lg md:border md:shadow-xs'
            : 'bg-card rounded-lg border shadow-xs',
        default => $stack ? 'md:rounded-md md:border' : 'rounded-md border',
    };

    $hasToolbar = $searchable || ! empty($perPageOptions) || $toggleableColumns || filled(trim($toolbar ?? ''));
@endphp

<div
    data-slot="server-table"
    @if ($selectable)
        x-data="{
            toggleAll(checked) {
                this.$root.querySelectorAll('tbody input[type=checkbox][data-row-select]').forEach(c => {
                    if (c.checked !== checked) { c.checked = checked; c.dispatchEvent(new Event('change', { bubbles: true })); }
                });
            },
        }"
    @endif
    {{ $attributes->twMerge('w-full') }}
>
    @if ($hasToolbar)
        <div class="flex flex-wrap items-center gap-2 pb-4">
            @if ($searchable)
                <x-blatui.input
                    type="search"
                    wire:model.live.debounce.300ms="{{ $searchModel }}"
                    placeholder="{{ $searchPlaceholder }}"
                    aria-label="{{ $searchPlaceholder }}"
                    class="max-w-xs"
                />
            @endif

            {{ $toolbar ?? '' }}

            @if (! empty($perPageOptions) || $toggleableColumns)
                <div class="ms-auto flex items-center gap-2">
                    @if (! empty($perPageOptions))
                        @php
                            $perPage = collect($perPageOptions)->mapWithKeys(fn ($label, $key) => is_int($key) && ! is_array($label)
                                ? [(string) $label => (string) $label]
                                : [(string) $key => (string) $label])->all();
                        @endphp
                        <x-blatui.select
                            wire:model.live="{{ $perPageModel }}"
                            :options="$perPage"
                            :aria-label="$perPageLabel"
                            size="sm"
                            class="w-auto"
                        />
                    @endif

                    @if ($toggleableColumns)
                        <x-blatui.dropdown-menu>
                            <x-blatui.dropdown-menu-trigger>
                                <x-blatui.button variant="outline" size="sm">
                                    <x-lucide-columns-3 class="size-4" aria-hidden="true" />
                                    <span class="max-sm:sr-only">{{ $columnsLabel }}</span>
                                    <x-lucide-chevron-down class="size-4 opacity-50" aria-hidden="true" />
                                </x-blatui.button>
                            </x-blatui.dropdown-menu-trigger>
                            <x-blatui.dropdown-menu-content align="end" class="w-56">
                                @foreach ($allCols as $col)
                                    @continue (! $col['hideable'])
                                    @php $isVisible = $visibleColumns === null || in_array($col['key'], $visibleColumns, true); @endphp

                                    <x-blatui.dropdown-menu-checkbox-item
                                        wire:key="col-{{ $col['key'] }}-{{ $isVisible ? 1 : 0 }}"
                                        :checked="$isVisible"
                                        wire:click="{{ $toggleColumnMethod }}('{{ $col['key'] }}')"
                                    >{{ $col['label'] }}</x-blatui.dropdown-menu-checkbox-item>
                                @endforeach
                            </x-blatui.dropdown-menu-content>
                        </x-blatui.dropdown-menu>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $wrapperClass }} {{ $stack ? 'md:overflow-x-auto' : 'relative w-full overflow-x-auto' }}"
        wire:loading.class.delay="opacity-60">
        <table data-slot="table" class="w-full caption-bottom text-sm {{ $stack ? 'max-md:block' : '' }}">
            @if ($caption)
                <caption class="{{ $captionVisible ? 'text-muted-foreground p-2 text-start text-sm' : 'sr-only' }}">{{ $caption }}</caption>
            @endif

            <thead data-slot="table-header" class="[&_tr]:border-b {{ $stack ? 'max-md:sr-only' : '' }}">
                <tr class="hover:bg-muted/50 border-b transition-colors">
                    @if ($selectable)
                        <th scope="col" class="h-10 w-10 px-2 text-start align-middle">
                            <span class="sr-only">{{ __('Select') }}</span>
                            <input type="checkbox" aria-label="{{ __('Select all rows on this page') }}"
                                @change="toggleAll($event.target.checked)"
                                class="border-input text-primary focus-visible:ring-ring/50 size-4 rounded-[4px] border shadow-xs outline-none focus-visible:ring-[3px]" />
                        </th>
                    @endif

                    @foreach ($cols as $col)
                        <th scope="col"
                            @if ($col['sortable']) aria-sort="{{ $sort === $col['key'] ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}" @endif
                            @if ($col['width']) style="width: {{ $col['width'] }}" @endif
                            class="text-foreground h-10 px-2 align-middle font-medium whitespace-nowrap {{ $alignClass($col['align']) }} {{ $col['class'] }}">
                            @if ($col['sortable'])
                                <button type="button" wire:click="{{ $sortMethod }}('{{ $col['key'] }}')"
                                    class="hover:text-foreground focus-visible:border-ring focus-visible:ring-ring/50 -ms-2 inline-flex items-center gap-1 rounded-md px-2 py-1 transition-colors outline-none focus-visible:ring-[3px] {{ $col['align'] === 'right' ? 'flex-row-reverse' : '' }}">
                                    {{ $col['label'] }}
                                    <span class="text-muted-foreground inline-flex" aria-hidden="true">
                                        @if ($sort === $col['key'])
                                            <x-dynamic-component :component="'lucide-'.($direction === 'asc' ? 'chevron-up' : 'chevron-down')" class="size-3.5" />
                                        @else
                                            <x-lucide-chevrons-up-down class="size-3.5 opacity-50" />
                                        @endif
                                    </span>
                                </button>
                            @else
                                {{ $col['label'] }}
                            @endif
                        </th>
                    @endforeach

                    @if ($hasActions)
                        <th scope="col" @class([
                            'text-foreground bg-background h-10 px-2 text-end align-middle font-medium whitespace-nowrap',
                            'sticky end-0 border-s' => $stickyActions,
                        ])>
                            <span class="sr-only">{{ $actionsLabel }}</span>
                        </th>
                    @endif
                </tr>
            </thead>

            <tbody data-slot="table-body" class="{{ $stack ? 'max-md:block md:[&_tr:last-child]:border-0' : '[&_tr:last-child]:border-0' }}">
                @forelse ($items as $row)
                    <tr wire:key="row-{{ data_get($row, $rowKey) }}"
                        class="hover:bg-muted/50 data-[state=selected]:bg-muted transition-colors {{ $stack ? 'max-md:divide-border max-md:mb-3 max-md:block max-md:divide-y max-md:rounded-md max-md:border md:border-b' : 'border-b' }}">
                        @if ($selectable)
                            <td class="w-10 px-2 align-middle {{ $stack ? 'max-md:flex max-md:justify-end max-md:p-2' : '' }}">
                                <input type="checkbox" data-row-select
                                    wire:model.live="{{ $selectModel }}"
                                    value="{{ data_get($row, $rowKey) }}"
                                    aria-label="{{ __('Select row') }}"
                                    class="border-input text-primary focus-visible:ring-ring/50 size-4 rounded-[4px] border shadow-xs outline-none focus-visible:ring-[3px]" />
                            </td>
                        @endif

                        @foreach ($cols as $col)
                            @php $value = data_get($row, $col['key']); @endphp
                            <td @class([
                                'p-2 align-middle whitespace-nowrap',
                                $alignClass($col['align']),
                                $col['class'],
                                'max-md:flex max-md:items-center max-md:justify-between max-md:gap-4 max-md:whitespace-normal max-md:px-4 max-md:py-2.5 max-md:text-end' => $stack,
                            ])>
                                @if ($stack)
                                    <span class="text-muted-foreground font-medium md:hidden" aria-hidden="true">{{ $col['label'] }}</span>
                                @endif
                                @if (isset($cellViews[$col['key']]))
                                    @include($cellViews[$col['key']], ['value' => $value, 'row' => $row])
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        @endforeach

                        @if ($hasActions)
                            <td @class([
                                'bg-background p-2 align-middle whitespace-nowrap',
                                'sticky end-0 border-s' => $stickyActions,
                                'max-md:flex max-md:justify-end max-md:px-4 max-md:py-3' => $stack,
                            ])>
                                @if ($actionsView)
                                    @include($actionsView, ['row' => $row])
                                @elseif ($actionsMode === 'dropdown')
                                    <div class="flex justify-end">
                                        <x-blatui.dropdown-menu>
                                            <x-blatui.dropdown-menu-trigger>
                                                <x-blatui.button variant="ghost" size="icon-xs" aria-label="{{ $actionsLabel }}">
                                                    <x-lucide-ellipsis class="size-4" aria-hidden="true" />
                                                </x-blatui.button>
                                            </x-blatui.dropdown-menu-trigger>
                                            <x-blatui.dropdown-menu-content align="end">
                                                @foreach ($actions as $action)
                                                    @continue (isset($action['visible']) && $action['visible'] instanceof \Closure && ! ($action['visible'])($row))
                                                    @php
                                                        $href = $actionHref($action, $row);
                                                        $variant = (($action['variant'] ?? null) === 'danger' || ($action['variant'] ?? null) === 'destructive') ? 'destructive' : 'default';
                                                        $wireClick = (! $href && ($action['method'] ?? null)) ? $action['method'].'('.$actionArgs($action, $row).')' : null;
                                                        $wireConfirm = $action['confirm'] ?? null;
                                                    @endphp
                                                    <x-blatui.dropdown-menu-item :href="$href" :variant="$variant" :wire:click="$wireClick" :wire:confirm="$wireConfirm">
                                                        @isset($action['icon'])
                                                            <x-dynamic-component :component="'lucide-'.$action['icon']" class="size-4" aria-hidden="true" />
                                                        @endisset
                                                        {{ $action['label'] ?? '' }}
                                                    </x-blatui.dropdown-menu-item>
                                                @endforeach
                                            </x-blatui.dropdown-menu-content>
                                        </x-blatui.dropdown-menu>
                                    </div>
                                @else
                                    <div class="flex items-center justify-end gap-1">
                                        @foreach ($actions as $action)
                                            @continue (isset($action['visible']) && $action['visible'] instanceof \Closure && ! ($action['visible'])($row))
                                            @php
                                                $href = $actionHref($action, $row);
                                                $wireClick = (! $href && ($action['method'] ?? null)) ? $action['method'].'('.$actionArgs($action, $row).')' : null;
                                                $wireConfirm = $action['confirm'] ?? null;
                                            @endphp
                                            <x-blatui.button
                                                :href="$href"
                                                :size="$action['size'] ?? 'sm'"
                                                :variant="$action['variant'] ?? 'ghost'"
                                                :color="$action['color'] ?? null"
                                                :class="$action['class'] ?? null"
                                                :wire:click="$wireClick"
                                                :wire:confirm="$wireConfirm"
                                                :aria-label="$action['label'] ?? __('Action')"
                                            >
                                                @isset($action['icon'])
                                                    <x-dynamic-component :component="'lucide-'.$action['icon']" class="size-4" aria-hidden="true" />
                                                @endisset
                                                @unless ($action['iconOnly'] ?? false)
                                                    <span @class(['sr-only sm:not-sr-only' => isset($action['icon'])])>{{ $action['label'] ?? '' }}</span>
                                                @endunless
                                            </x-blatui.button>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr class="{{ $stack ? 'max-md:block' : '' }}">
                        <td colspan="{{ $colspan }}" class="h-24 text-center align-middle {{ $stack ? 'max-md:block' : '' }}">
                            <div class="text-muted-foreground flex flex-col items-center justify-center gap-2 py-6">
                                <x-dynamic-component :component="'lucide-'.$emptyIcon" class="size-6 opacity-60" aria-hidden="true" />
                                <span>{{ $emptyText }}</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($isPaginator && $paginate && $rows->hasPages())
        <div class="pt-4">
            {{ $rows->links() }}
        </div>
    @endif
</div>
