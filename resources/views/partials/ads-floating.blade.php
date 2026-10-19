@if (\App\Support\Ads::enabled() && \App\Support\Ads::slot('mobile_float') && ! empty(\App\Support\Ads::slot('mobile_float')['key']))
<div id="fc-ad-float" class="fc-ad-float" role="complementary" aria-label="Advertisement">
    <button type="button" class="fc-ad-float__close" data-ad-float-close aria-label="Close advertisement">
        <svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M2 2l12 12M14 2L2 14" /></svg>
    </button>
    <x-ads.slot name="mobile_float" sticky class="mx-auto" />
</div>
@endif
