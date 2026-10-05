{{-- Shared bulk action config carrier. Behavior is delegated by resources/js/admin.js. --}}
<script type="application/json" data-admin-bulk-config>{!! json_encode($bulkConfig ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
