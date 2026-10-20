@if (\App\Support\Ads::enabled() && ($miniSlot = $slot ?? \App\Support\Ads::pick('inline')) && ($cfg = \App\Support\Ads::slot($miniSlot)) && ! empty($cfg['key']))
    <div class="fc-ad-mini lg:hidden text-center" role="complementary" aria-label="Advertisement">
        <x-ads.slot :name="$miniSlot" class="mx-auto" />
    </div>
@endif
