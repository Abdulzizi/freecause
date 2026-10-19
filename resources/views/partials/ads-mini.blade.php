@if (\App\Support\Ads::enabled() && ($cfg = \App\Support\Ads::slot($slot ?? '')) && ! empty($cfg['key']))
    <div class="fc-ad-mini lg:hidden text-center" role="complementary" aria-label="Advertisement">
        <x-ads.slot :name="$slot" class="mx-auto" />
    </div>
@endif
