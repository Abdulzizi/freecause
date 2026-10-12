{{--
    Admin section: a small uppercase heading above a card.
    Props: title (required string), tabClass/boxClass (optional class strings).
    The default slot is rendered inside the card.
--}}
@props([
    'title',
    'tabClass' => '',
    'boxClass' => '',
])

<div {{ $attributes->class(['flex! items-center! gap-2! mb-2! font-semibold! text-[13px]! uppercase! tracking-[0.05em]! text-ink!', $tabClass]) }}>
    {{ $title }}
</div>

<x-blatui.card class="p-4! mb-5! {{ $boxClass }}">
    {{ $slot }}
</x-blatui.card>
