@php($infeedSlot = $slot ?? \App\Support\Ads::pick('infeed'))
@if (\App\Support\Ads::enabled() && $infeedSlot && ($cfg = \App\Support\Ads::slot($infeedSlot)) && ! empty($cfg['key']) && \App\Support\Ads::renderOnce($infeedSlot))
    <div class="fc-ad-infeed lg:hidden text-center" role="complementary" aria-label="Advertisement">
        <x-ads.slot :name="$infeedSlot" claimed class="mx-auto" />
    </div>
@endif
