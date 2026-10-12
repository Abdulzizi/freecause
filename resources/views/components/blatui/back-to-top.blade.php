@props([
    'threshold' => 300,
    'variant' => 'primary',
    'demo' => false,
])

@php
    $variants = [
        'primary' => 'bg-primary text-primary-foreground hover:bg-primary/90',
        'subtle' => 'bg-card text-foreground border hover:bg-accent hover:text-accent-foreground',
    ];
    $variantClasses = $variants[$variant] ?? $variants['primary'];

    $position = $demo ? 'static' : 'fixed bottom-6 end-6 z-40';
@endphp

<button
    data-slot="back-to-top"
    type="button"
    aria-label="{{ __('Back to top') }}"
    x-data="{
        shown: @js((bool) $demo),
        demo: @js((bool) $demo),
        threshold: @js((int) $threshold),
        reduced: false,
        init() {
            if (this.demo) return;
            this.reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            this.onScroll();
        },
        onScroll() {
            if (this.demo) return;
            this.shown = window.scrollY > this.threshold;
        },
        toTop() {
            window.scrollTo({ top: 0, behavior: this.reduced ? 'auto' : 'smooth' });
        },
    }"
    @scroll.window.passive="onScroll()"
    @resize.window.passive="onScroll()"
    x-show="shown"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    @click="toTop()"
    {{ $attributes->twMerge($position.' inline-flex size-11 shrink-0 cursor-pointer items-center justify-center rounded-full shadow-lg transition-colors outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px] '.$variantClasses) }}
>
    <x-lucide-arrow-up class="size-5" aria-hidden="true" />
</button>
