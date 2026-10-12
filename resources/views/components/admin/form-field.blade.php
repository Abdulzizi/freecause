
@props([
    'label',
    'for' => null,
    'hint' => null,
    'error' => null,
])

<div {{ $attributes->class(['grid grid-cols-1 gap-1.5 items-start py-1.5 md:grid-cols-[160px_1fr] md:gap-3 md:items-center']) }}>
    <label @if ($for) for="{{ $for }}" @endif class="font-semibold text-[13px] text-ink">{{ $label }}</label>
    <div class="min-w-0">
        {{ $slot }}
        @if (filled($hint))
            <div class="mt-1 text-[12px] text-muted">{{ $hint }}</div>
        @endif
        @if (filled($error))
            <div class="mt-1 text-[12px] text-danger" role="alert">{{ $error }}</div>
        @endif
    </div>
</div>
