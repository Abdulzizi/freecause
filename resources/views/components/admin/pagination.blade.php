{{--
    Thin admin wrapper around the existing simple-window-pagination partial.
    Props: p (required Laravel paginator instance). Existing pagination markup is preserved verbatim.
--}}
@props(['p'])

@if ($p && method_exists($p, 'hasPages') && $p->hasPages())
    @include('admin.partials.simple-window-pagination', ['p' => $p])
@endif
