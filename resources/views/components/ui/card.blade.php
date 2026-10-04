@props([
    'header' => null,
])

<div {{ $attributes->merge(['class' => 'fcui-card']) }}>
    @if ($header)
        <div class="fcui-card__header">
            {{ $header }}
        </div>
    @endif

    <div class="fcui-card__body">
        {{ $slot }}
    </div>
</div>
