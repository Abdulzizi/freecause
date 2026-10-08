{{--
    Admin bulk-selection toolbar. Preserves the existing fc-bulk-* selectors and ! utility classes.
    Admin button mapping: danger is destructive red, secondary preserves white/grey toolbar controls,
    and primary is brand red; legacy grey save buttons should use secondary or explicit grey utilities.
    Optional props: actionRoute, banRoute, deleteRoute, unbanRoute, noun, banLabel, banConfirm,
    banWithDetails, emptyMsg, checkboxSelector. The default slot may contain custom action controls
    such as existing .bulk-action buttons.
    The consolidated admin bulk module is registered once even if multiple legacy adapters render it.
--}}
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

<div class="flex! items-center! gap-2.5! flex-wrap! max-md:gap-1.5!">
    <div class="flex! items-center! gap-1!">
        <a href="#" data-bulk="all" title="Select all" aria-label="Select all" class="inline-flex! items-center! gap-1! no-underline! text-muted! p-[3px_6px]! rounded-[3px]! transition-colors! hover:bg-[#f0f0f0]!">
            <span class="inline-block! w-3.5! text-center! text-xs!"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
            <span class="max-md:hidden!">All</span>
        </a>

        <a href="#" data-bulk="none" title="Deselect all" aria-label="Deselect all" class="inline-flex! items-center! gap-1! no-underline! text-muted! p-[3px_6px]! rounded-[3px]! transition-colors! hover:bg-[#f0f0f0]!">
            <span class="inline-block! w-3.5! text-center! text-xs!"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18"></rect></svg></span>
            <span class="max-md:hidden!">None</span>
        </a>

        <a href="#" data-bulk="invert" title="Invert selection" aria-label="Invert selection" class="inline-flex! items-center! gap-1! no-underline! text-muted! p-[3px_6px]! rounded-[3px]! transition-colors! hover:bg-[#f0f0f0]!">
            <span class="inline-block! w-3.5! text-center! text-xs!"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 3L3 21"></path><path d="M3 3L21 21"></path></svg></span>
            <span class="max-md:hidden!">Invert</span>
        </a>
    </div>

    <span class="fc-bulk-count items-center! py-0.5! px-2! bg-[#e8f0fe]! text-[#1a5fb4]! rounded-[10px]! text-[11px]! font-semibold! whitespace-nowrap! leading-[1.6]! ml-2!" id="fc-bulk-count" aria-live="polite"></span>

    {{ $slot }}

    @if ($banRoute)
        <div class="flex! items-center! gap-1! ml-1! pl-2.5! border-l! border-border!">
            <a href="#" data-bulk="ban"
               data-confirm="{{ $banConfirm }}"
               data-ban-details="{{ $banWithDetails ? '1' : '0' }}"
               title="{{ $banLabel }}"
               aria-label="{{ $banLabel }}"
               class="inline-flex! items-center! gap-1! no-underline! text-danger! p-[3px_6px]! rounded-[3px]! transition-colors! hover:bg-[#fff0f0]!">
                <span class="inline-block! w-3.5! text-center! text-xs!"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg></span>
                <span class="max-md:hidden!">{{ $banLabel }}</span>
            </a>
        </div>
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
