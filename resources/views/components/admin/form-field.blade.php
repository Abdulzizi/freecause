{{--
    Responsive admin label/control row.
    Props: label (required string or viewable), for (optional control id), hint/error (optional text).
    The default slot must contain the form control; callers retain control of its name, value, and validation.
--}}
@props([
    'label',
    'for' => null,
    'hint' => null,
    'error' => null,
])

<div {{ $attributes->class(['grid! grid-cols-1! gap-1.5! items-start! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!']) }}>
    <label @if ($for) for="{{ $for }}" @endif class="font-bold! text-xs! md:text-sm!">{{ $label }}</label>
    <div class="min-w-0!">
        {{ $slot }}
        @if (filled($hint))
            <div class="mt-1! text-[11px]! text-[#777]!">{{ $hint }}</div>
        @endif
        @if (filled($error))
            <div class="mt-1! text-[11px]! text-[#c00]!" role="alert">{{ $error }}</div>
        @endif
    </div>
</div>
