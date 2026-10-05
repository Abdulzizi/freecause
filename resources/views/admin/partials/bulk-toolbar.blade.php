{{-- Legacy include adapter; existing list-table-box callers keep their current API. --}}
<x-admin.bulk-toolbar
    :ban-route="$banRoute ?? null"
    :ban-label="$banLabel ?? 'Ban'"
    :ban-confirm="$banConfirm ?? 'Apply action to selected items?'"
    :ban-with-details="$banWithDetails ?? false"
    :empty-msg="$emptyMsg ?? 'No items selected'"
    :checkbox-selector="$checkboxSelector ?? '.bulk-checkbox'"
/>
