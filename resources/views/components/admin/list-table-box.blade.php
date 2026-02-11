<div class="fc-box" style="margin-top:10px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
        @if(!empty($p) && $p->hasPages())
            @include('admin.partials.simple-window-pagination', ['p' => $p])
        @endif
    </div>

    <table style="width:100%; border-collapse:collapse;">
        <thead>
            {{ $thead }}
        </thead>
        <tbody>
            {{ $tbody }}
        </tbody>
    </table>

    @if(!empty($bulk))
        <div style="margin-top:6px; font-size:11px; color:#555;">
            @include('admin.partials.bulk-toolbar', $bulk)
        </div>
    @endif
</div>
