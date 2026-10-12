
@props([
    'href' => null,
    'isActive' => false,
    'variant' => 'default',
    'size' => 'default',
    'tooltip' => null,
])

@php
    $base = "peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-start text-sm outline-none ring-sidebar-ring transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2 active:bg-sidebar-accent active:text-sidebar-accent-foreground disabled:pointer-events-none disabled:opacity-50 group-has-[[data-sidebar=menu-action]]/menu-item:pe-8 aria-disabled:pointer-events-none aria-disabled:opacity-50 data-[active=true]:bg-sidebar-accent data-[active=true]:font-medium data-[active=true]:text-sidebar-accent-foreground group-data-[collapsible=icon]:size-8! [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0";
    $variants = [
        'default' => 'hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
        'outline' => 'bg-background shadow-[0_0_0_1px_var(--sidebar-border)] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground hover:shadow-[0_0_0_1px_var(--sidebar-accent)]',
    ];
    $sizes = [
        'default' => 'h-8 text-sm',
        'sm' => 'h-7 text-xs',
        'lg' => 'h-12 text-sm',
    ];
    $iconPad = $size === 'lg' ? 'group-data-[collapsible=icon]:p-0!' : 'group-data-[collapsible=icon]:p-2!';
    $classes = $base.' '.($variants[$variant] ?? $variants['default']).' '.($sizes[$size] ?? $sizes['default']).' '.$iconPad;

    $tipState = 'tipOpen && $data.collapsed';
@endphp

@if ($tooltip)

    <div
        data-slot="sidebar-menu-button-tooltip"
        x-data="{ tipOpen: false }"
        x-id="['blat-tooltip']"
        @mouseenter="tipOpen = true"
        @mouseleave="tipOpen = false"
        @focusin="tipOpen = true"
        @focusout="tipOpen = false"

        @if ($isActive) data-active="true" @endif
        class="peer/menu-button block w-full"
    >
@endif

@if ($href)
    <a
        href="{{ $href }}"
        data-slot="sidebar-menu-button"
        data-sidebar="menu-button"
        data-size="{{ $size }}"
        @if ($isActive) data-active="true" aria-current="page" @endif
        @if ($tooltip) x-ref="trigger" :aria-describedby="{{ $tipState }} ? $id('blat-tooltip') : null" @endif
        {{ $attributes->twMerge($classes) }}
    >{{ $slot }}</a>
@else
    <button
        type="button"
        data-slot="sidebar-menu-button"
        data-sidebar="menu-button"
        data-size="{{ $size }}"
        @if ($isActive) data-active="true" @endif
        @if ($tooltip) x-ref="trigger" :aria-describedby="{{ $tipState }} ? $id('blat-tooltip') : null" @endif
        {{ $attributes->twMerge($classes) }}
    >{{ $slot }}</button>
@endif

@if ($tooltip)
        <x-blatui.tooltip-content side="right" :state="$tipState" :arrow="false">{{ $tooltip }}</x-blatui.tooltip-content>
    </div>
@endif
