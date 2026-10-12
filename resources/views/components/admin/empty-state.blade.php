
@props([
    'message',
    'title' => null,
])

<div {{ $attributes->class(['flex! flex-col! items-center! justify-center! gap-1.5! px-3! py-8! text-center!']) }}>
    <x-lucide-inbox class="size-6! text-muted!" aria-hidden="true" />
    @if (filled($title))
        <div class="font-semibold! text-sm! text-ink!">{{ $title }}</div>
    @endif
    <div class="text-[13px]! text-muted!">{{ $message }}</div>
    @if (trim((string) $slot) !== '')
        <div class="mt-2!">{{ $slot }}</div>
    @endif
</div>
