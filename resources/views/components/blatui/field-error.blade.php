@props([
    'messages' => null,
])

@php
    $items = collect(is_array($messages) ? $messages : ($messages !== null && $messages !== '' ? [$messages] : []))
        ->map(fn ($m) => is_array($m) ? ($m['message'] ?? null) : $m)
        ->filter(fn ($m) => filled($m))
        ->unique()
        ->values();
    $hasSlot = trim($slot) !== '';
@endphp

@if ($hasSlot || $items->isNotEmpty())
    <div role="alert" data-slot="field-error" {{ $attributes->twMerge('text-destructive text-sm font-normal') }}>
        @if ($hasSlot)
            {{ $slot }}
        @elseif ($items->count() === 1)
            {{ $items->first() }}
        @else
            <ul class="ms-4 flex list-disc flex-col gap-1">
                @foreach ($items as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
