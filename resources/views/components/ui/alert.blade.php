@props([
    'variant' => 'info',
    'dismissible' => false,
])

@php
    $variant = in_array($variant, ['success', 'error', 'warning', 'info'], true) ? $variant : 'info';
    $role = in_array($variant, ['error', 'warning'], true) ? 'alert' : 'status';
    $variantClass = "fc-alert--{$variant}";
@endphp

<div
    role="{{ $role }}"
    data-alert
    {{ $attributes->merge(['class' => "fc-alert {$variantClass}"]) }}
>
    <span class="fc-alert__icon" aria-hidden="true">
        @switch($variant)
            @case('success')
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.07 7.14a1 1 0 0 1-1.42 0l-3.54-3.57a1 1 0 1 1 1.42-1.41l2.83 2.86 6.36-6.42a1 1 0 0 1 1.414-.014z" clip-rule="evenodd" /></svg>
                @break
            @case('error')
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zM8.7 7.29a1 1 0 0 0-1.41 1.42L8.58 10l-1.3 1.29a1 1 0 1 0 1.42 1.42L10 11.41l1.29 1.3a1 1 0 0 0 1.42-1.42L11.41 10l1.3-1.29a1 1 0 0 0-1.42-1.42L10 8.58 8.71 7.29z" clip-rule="evenodd" /></svg>
                @break
            @case('warning')
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.1c.765-1.36 2.72-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM10 7a1 1 0 0 0-1 1v2a1 1 0 1 0 2 0V8a1 1 0 0 0-1-1zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" clip-rule="evenodd" /></svg>
                @break
            @default
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm1-11a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-1 3a1 1 0 0 0-1 1v3a1 1 0 1 0 2 0v-3a1 1 0 0 0-1-1z" clip-rule="evenodd" /></svg>
        @endswitch
    </span>

    <div class="fc-alert__content">
        {{ $slot }}
    </div>

    @if ($dismissible)
        <button
            type="button"
            class="fc-alert__dismiss"
            data-alert-dismiss
            aria-label="{{ __('ui.dismiss') }}"
        >
            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414z" clip-rule="evenodd" /></svg>
        </button>
    @endif
</div>
