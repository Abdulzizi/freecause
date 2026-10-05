{{--
    Compact empty-state message for admin panels and tables.
    Props: message (required string), title (optional string).
    The default slot is an optional action area.
--}}
@props([
    'message',
    'title' => null,
])

<div {{ $attributes->class(['px-3! py-3! text-xs! text-[#777]!']) }}>
    @if (filled($title))
        <div class="mb-1! font-semibold! text-[#555]!">{{ $title }}</div>
    @endif
    <div>{{ $message }}</div>
    @if (trim((string) $slot) !== '')
        <div class="mt-2!">{{ $slot }}</div>
    @endif
</div>
