{{--
    Admin page heading with an optional count, context line, and right-aligned actions.
    Props: title (required string), count (optional scalar), context (optional string).
    The default slot is reserved for page actions.
    Keep the legacy heading spacing and the admin `!` utility convention when adopting.
--}}
@props([
    'title',
    'count' => null,
    'context' => null,
])

<div {{ $attributes->class(['flex! items-start! justify-between! gap-3! flex-wrap! mb-2!']) }}>
    <div class="min-w-0!">
        <h1 class="m-0! text-lg! max-md:text-base!">
            {{ $title }}@if ($count !== null) <span>({{ is_numeric($count) ? number_format((float) $count) : $count }})</span>@endif
        </h1>
        @if (filled($context))
            <p class="m-0! mt-1! text-xs! text-[#777]!">{{ $context }}</p>
        @endif
    </div>

    @if (trim((string) $slot) !== '')
        <div class="flex! items-center! gap-2! flex-wrap!">{{ $slot }}</div>
    @endif
</div>
