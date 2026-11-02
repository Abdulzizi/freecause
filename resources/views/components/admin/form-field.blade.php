
@props([
    'label',
    'for' => null,
    'hint' => null,
    'error' => null,
])

@php
    $control = (string) $slot;
    if (! $for && filled($label)) {
        $done = false;
        $control = preg_replace_callback(
            '/<(input|select|textarea)\b([^>]*)>/i',
            function ($m) use (&$done, $label) {
                if ($done || preg_match('/(?<![\w:-])type\s*=\s*(?:"(?:checkbox|radio|hidden)"|\'(?:checkbox|radio|hidden)\'|(?:checkbox|radio|hidden)\b)/i', $m[2])) {
                    return $m[0];
                }
                $done = true;

                return preg_match('/(?<![\w-])aria-label\b/i', $m[2])
                    ? $m[0]
                    : '<' . $m[1] . ' aria-label="' . e($label) . '"' . $m[2] . '>';
            },
            $control
        ) ?? (string) $slot;
    }
@endphp

<div {{ $attributes->class(['grid grid-cols-1 gap-1.5 items-start py-1.5 md:grid-cols-[160px_1fr] md:gap-3 md:items-center']) }}>
    <label @if ($for) for="{{ $for }}" @endif class="font-semibold text-sm text-ink">{{ $label }}</label>
    <div class="min-w-0">
        {!! $control !!}
        @if (filled($hint))
            <div class="mt-1 text-xs text-muted">{{ $hint }}</div>
        @endif
        @if (filled($error))
            <div class="mt-1 text-xs text-danger" role="alert">{{ $error }}</div>
        @endif
    </div>
</div>
