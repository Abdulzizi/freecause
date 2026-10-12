
<div data-slot="sidebar-content" data-sidebar="content" {{ $attributes->twMerge('flex min-h-0 flex-1 flex-col gap-2 overflow-auto group-data-[collapsible=icon]:overflow-x-hidden group-data-[collapsible=icon]:[scrollbar-width:none] group-data-[collapsible=icon]:[&::-webkit-scrollbar]:hidden') }}>
    {{ $slot }}
</div>
