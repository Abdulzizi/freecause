@props([
    'id',
    'title' => null,
    'actions' => [],
])

<dialog id="{{ $id }}" class="fcui-sheet" data-sheet aria-labelledby="{{ $id }}-title">
    <div class="fcui-sheet__grip" data-sheet-grip aria-hidden="true"></div>

    <div class="fcui-sheet__head">
        <span id="{{ $id }}-title">{{ $title ?? 'FreeCause' }}</span>
        <button type="button" class="fcui-sheet__close" data-sheet-close aria-label="{{ __('common.close') }}">
            <x-lucide-x aria-hidden="true" />
        </button>
    </div>

    <div class="fcui-sheet-list">
        @foreach ($actions as $action)
            @if (! empty($action['form']))
                <form method="POST" action="{{ $action['form'] }}" class="contents">
                    @csrf
                    <button type="submit" class="fcui-sheet-list__item w-full">
                        @if (! empty($action['icon']))
                            <x-dynamic-component :component="'lucide-'.$action['icon']" aria-hidden="true" />
                        @endif
                        <span>{{ $action['label'] }}</span>
                    </button>
                </form>
            @elseif (! empty($action['href']))
                <a
                    class="fcui-sheet-list__item"
                    href="{{ $action['href'] }}"
                    @if (! empty($action['external'])) target="_blank" rel="noopener noreferrer" @endif
                    @if (! empty($action['copy'])) data-copy-link="{{ $action['copy'] }}" @endif
                >
                    @if (! empty($action['icon']))
                        <x-dynamic-component :component="'lucide-'.$action['icon']" aria-hidden="true" />
                    @endif
                    <span>{{ $action['label'] }}</span>
                </a>
            @elseif (! empty($action['sheet']))
                <button type="button" class="fcui-sheet-list__item w-full" data-sheet-open="{{ $action['sheet'] }}">
                    @if (! empty($action['icon']))
                        <x-dynamic-component :component="'lucide-'.$action['icon']" aria-hidden="true" />
                    @endif
                    <span>{{ $action['label'] }}</span>
                </button>
            @endif
        @endforeach

        {{ $slot }}
    </div>
</dialog>
