
@props([
    'title',
    'count' => null,
    'context' => null,
])

<div {{ $attributes->class(['flex! items-start! justify-between! gap-3! flex-wrap! mb-5!']) }}>
    <div class="min-w-0!">
        <h1 class="m-0! flex! items-center! gap-2! text-2xl! font-bold! tracking-tight! text-foreground! max-md:text-xl!">
            <span class="truncate!">{{ $title }}</span>
            @if ($count !== null)
                <x-blatui.badge variant="outline" class="text-muted! border-border!">{{ is_numeric($count) ? number_format((float) $count) : $count }}</x-blatui.badge>
            @endif
        </h1>
        @if (filled($context))
            <p class="m-0! mt-1! text-[13px]! text-muted!">{{ $context }}</p>
        @endif
    </div>

    @if (trim((string) $slot) !== '')
        <div class="flex! items-center! gap-2! flex-wrap!">{{ $slot }}</div>
    @endif
</div>
