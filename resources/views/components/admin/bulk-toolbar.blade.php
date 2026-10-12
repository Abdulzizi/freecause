
@props([
    'actionRoute' => null,
    'banRoute' => null,
    'deleteRoute' => null,
    'unbanRoute' => null,
    'restoreRoute' => null,
    'noun' => 'items',
    'banLabel' => 'Ban',
    'banConfirm' => 'Apply action to selected items?',
    'banWithDetails' => false,
    'emptyMsg' => 'No items selected',
    'checkboxSelector' => '.bulk-checkbox',
])

<div class="flex items-center gap-2.5 flex-wrap max-md:gap-1.5">
    <x-blatui.button-group>
        <x-blatui.button variant="outline" size="sm" href="#" data-bulk="all" title="Select all" aria-label="Select all">
            <x-lucide-check-check aria-hidden="true" />
            <span class="max-md:hidden">All</span>
        </x-blatui.button>
        <x-blatui.button variant="outline" size="sm" href="#" data-bulk="none" title="Deselect all" aria-label="Deselect all">
            <x-lucide-square aria-hidden="true" />
            <span class="max-md:hidden">None</span>
        </x-blatui.button>
        <x-blatui.button variant="outline" size="sm" href="#" data-bulk="invert" title="Invert selection" aria-label="Invert selection">
            <x-lucide-arrow-left-right aria-hidden="true" />
            <span class="max-md:hidden">Invert</span>
        </x-blatui.button>
    </x-blatui.button-group>

    <span class="fc-bulk-count items-center py-0.5 px-2 rounded-[10px] text-[11px] font-semibold whitespace-nowrap leading-[1.6] ml-2" id="fc-bulk-count" aria-live="polite"></span>

    {{ $slot }}

    @if ($banRoute)
        <x-blatui.button variant="outline" size="sm" href="#" data-bulk="ban"
            data-confirm="{{ $banConfirm }}"
            data-ban-details="{{ $banWithDetails ? '1' : '0' }}"
            title="{{ $banLabel }}"
            aria-label="{{ $banLabel }}"
            class="text-danger border-danger hover:bg-destructive/10">
            <x-lucide-ban aria-hidden="true" />
            <span class="max-md:hidden">{{ $banLabel }}</span>
        </x-blatui.button>
    @endif
</div>

@include('admin.partials.bulk-module', ['bulkConfig' => [
    'actionRoute' => $actionRoute,
    'banRoute' => $banRoute,
    'deleteRoute' => $deleteRoute,
    'unbanRoute' => $unbanRoute,
    'restoreRoute' => $restoreRoute,
    'noun' => $noun,
    'banConfirm' => $banConfirm,
    'banWithDetails' => $banWithDetails,
    'banLabel' => $banLabel,
    'checkboxSelector' => $checkboxSelector,
    'emptyMsg' => $emptyMsg,
    'csrfToken' => csrf_token(),
]])
