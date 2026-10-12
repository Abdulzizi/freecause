@props([
    'title' => null,
    'lead' => null,
    'actions' => null,
    'breadcrumb' => null,
    'band' => false,
])

<div @class(['fcui-page-head', 'fcui-section--band' => $band])>
    <x-ui.container>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                @if ($breadcrumb)
                    <x-ui.breadcrumb :items="$breadcrumb" class="mb-2" />
                @endif

                @if ($title)
                    <h1 class="fcui-page-head__title">{{ $title }}</h1>
                @endif

                @if ($lead)
                    <p class="fcui-page-head__lead">{{ $lead }}</p>
                @endif

                {{ $slot }}
            </div>

            @if ($actions)
                <div class="fcui-page-head__actions">{{ $actions }}</div>
            @endif
        </div>
    </x-ui.container>
</div>
