@props([
    'title' => 'filter',
    'action' => '',
    'reset' => '',
    'activeCount' => 0,
])

<div class="fc-tab" style="display:flex; align-items:center; gap:8px;">
    {{ $title }}
    @if ($activeCount > 0)
        <span class="fc-filter-count show">{{ $activeCount }} active</span>
    @endif
</div>
<div class="fc-box">
    <form method="get" action="{{ $action }}"
        style="margin:0; display:flex; gap:6px; align-items:center; flex-wrap:wrap;">

        {{ $slot }}

        <button class="fc-btn" type="submit">apply</button>
        <a class="fc-btn" href="{{ $reset }}" style="text-decoration:none;">reset</a>
    </form>
</div>
