@props([
    'header' => null,
])

<div {{ $attributes->merge(['class' => 'fc-card']) }}>
    @if ($header)
        <div class="fc-card__header">
            {{ $header }}
        </div>
    @endif

    <div class="fc-card__body">
        {{ $slot }}
    </div>
</div>
