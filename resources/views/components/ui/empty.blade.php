@props([
    'title' => null,
    'text' => null,
])

<div {{ $attributes->merge(['class' => 'fcui-empty']) }}>
    @isset($icon)
        <div class="fcui-empty__icon">{{ $icon }}</div>
    @endisset

    @if ($title)
        <p class="fcui-empty__title">{{ $title }}</p>
    @endif

    @if ($text)
        <p class="fcui-empty__text">{{ $text }}</p>
    @endif

    @isset($action)
        <div class="fcui-empty__action">{{ $action }}</div>
    @endisset
</div>
