@php($anchorSlot = \App\Support\Ads::pick('anchor'))
@if ($anchorSlot && ($cfg = \App\Support\Ads::slot($anchorSlot)) && ! empty($cfg['key']))
<div id="fc-ad-anchor" class="fc-ad-anchor" role="complementary" aria-label="Advertisement">
    <button type="button" class="fc-ad-anchor__close" data-ad-anchor-close aria-label="Close advertisement">
        <svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M2 2l12 12M14 2L2 14" /></svg>
    </button>
    <x-ads.slot :name="$anchorSlot" sticky class="mx-auto" />
</div>
@endif
