{{--
    Existing admin tab + boxed-content pattern.
    Props: title (required string), tabClass/boxClass (optional class strings).
    The default slot is rendered inside the box. Additional attributes are applied to the tab.
--}}
@props([
    'title',
    'tabClass' => '',
    'boxClass' => '',
])

<div {{ $attributes->class(['fc-tab', 'flex! items-center! gap-2!', $tabClass]) }}>
    {{ $title }}
</div>
<div @class(['fc-box', $boxClass])>
    {{ $slot }}
</div>
