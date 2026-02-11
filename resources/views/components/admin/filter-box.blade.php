<div class="fc-tab">{{ $title }}</div>
<div class="fc-box">
    <form method="get" action="{{ $action }}"
        style="margin:0; display:flex; gap:6px; align-items:center; flex-wrap:wrap;">

        {{ $slot }}

        <button class="fc-btn" type="submit">apply</button>
        <a class="fc-btn" href="{{ $reset }}" style="text-decoration:none;">reset</a>
    </form>
</div>
