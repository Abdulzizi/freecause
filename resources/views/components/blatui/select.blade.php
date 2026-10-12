
@props([
    'name' => null,
    'value' => '',
    'native' => false,
    'size' => 'default',
    'multiple' => false,
    'options' => null,
    'placeholder' => '',
    'color' => null,
    'indicator' => 'check',
])

@php
    $hasOptions = is_array($options) && count($options) > 0;
    $normalized = [];
    if ($hasOptions) {
        foreach ($options as $k => $v) {
            if (is_int($k)) {
                $normalized[(string) $v] = (string) $v;
            } else {
                $normalized[(string) $k] = (string) $v;
            }
        }
    }

    $selectedValues = collect(is_array($value) ? $value : (($value === '' || $value === null) ? [] : [$value]))
        ->map(fn ($v) => (string) $v)->values();
    $initialValue = $multiple ? $selectedValues : (string) $value;

    $colorStyle = $color ? "--ring: {$color}; --primary: {$color}; --primary-foreground: #ffffff;" : '';
    $userStyle = (string) $attributes->get('style', '');
    $style = trim($colorStyle.($colorStyle && $userStyle ? ' ' : '').$userStyle);
    $attributes = $attributes->except('style');

    $wireModel = \Illuminate\View\ComponentAttributeBag::hasMacro('wire') ? $attributes->wire('model') : null;
    $hasWire = $wireModel && is_string($wireModel->value()) && $wireModel->value() !== '';
    if (! $native && $hasWire) {
        $attributes = $attributes->whereDoesntStartWith('wire:model')->merge(array_filter([
            'data-blat-model' => $wireModel->value(),
            'data-blat-model-live' => $wireModel->hasModifier('live') ? '1' : null,
        ]));
    }
@endphp

@if ($native)
    @php
        $nativeSizes = ['sm' => 'h-8 text-sm', 'default' => 'h-9', 'lg' => 'h-10'];
        $nativeSize = $nativeSizes[$size] ?? $nativeSizes['default'];
    @endphp
    <select
        @if ($name) name="{{ $name }}{{ $multiple ? '[]' : '' }}" @endif
        @if ($multiple) multiple @endif
        data-slot="select"
        data-size="{{ $size }}"
        @if ($style) style="{{ $style }}" @endif
        {{ $attributes->twMerge('blat-select '.($multiple ? 'h-auto min-h-9 py-1' : $nativeSize)) }}
    >
        @if ($hasOptions)
            @if (! $multiple && $placeholder !== '')
                <option value="" disabled @selected($value === '')>{{ $placeholder }}</option>
            @endif
            @foreach ($normalized as $val => $lab)
                <option value="{{ $val }}" @selected($selectedValues->contains((string) $val))>{{ $lab }}</option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>
@else
    <div
        data-slot="select"
        x-data="blatSelect({ model: $blatModel(@js($initialValue)), wired: @js($hasWire), multiple: @js((bool) $multiple) })"
        x-id="['blat-listbox']"
        @if ($style) style="{{ $style }}" @endif
        {{ $attributes->twMerge('relative') }}
    >
        @if ($name)
            @if ($multiple)
                <template x-for="v in value" :key="v">
                    <input type="hidden" name="{{ $name }}[]" :value="v">
                </template>
            @else
                <input type="hidden" name="{{ $name }}" :value="value">
            @endif
        @endif
        @if ($hasOptions)
            <x-blatui.select-trigger :class="'w-full'.($multiple ? ' data-[size=default]:h-auto min-h-9 py-1' : '')" :ariaLabel="$placeholder !== '' ? $placeholder : __('Select option')">
                <x-blatui.select-value :placeholder="$placeholder" />
            </x-blatui.select-trigger>
            <x-blatui.select-content :indicator="$indicator">
                @foreach ($normalized as $val => $lab)
                    <x-blatui.select-item :value="$val">{{ $lab }}</x-blatui.select-item>
                @endforeach
            </x-blatui.select-content>
        @else
            {{ $slot }}
        @endif
    </div>
@endif
