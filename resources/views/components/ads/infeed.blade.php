@props(['name'])
@if (\App\Support\Ads::enabled() && $name && ($cfg = \App\Support\Ads::slot($name)) && ! empty($cfg['key']) && \App\Support\Ads::renderOnce($name))
    <div class="fc-ad-infeed lg:hidden text-center" role="complementary" aria-label="Advertisement">
        <x-ads.slot :name="$name" claimed class="mx-auto" />
    </div>
@endif
