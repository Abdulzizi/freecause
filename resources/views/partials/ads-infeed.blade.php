@php($infeedSlot = $slot ?? \App\Support\Ads::pick('infeed'))
@if (\App\Support\Ads::enabled() && $infeedSlot && ($cfg = \App\Support\Ads::slot($infeedSlot)) && ! empty($cfg['key']))
    <div class="fc-ad-infeed lg:hidden text-center" role="complementary" aria-label="Advertisement">
        <x-ads.slot :name="$infeedSlot" class="mx-auto" />
    </div>
@endif
