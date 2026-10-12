
<x-admin.bulk-toolbar
    :ban-route="$banRoute ?? null"
    :ban-label="$banLabel ?? 'Ban'"
    :ban-confirm="$banConfirm ?? 'Apply action to selected items?'"
    :ban-with-details="$banWithDetails ?? false"
    :empty-msg="$emptyMsg ?? 'No items selected'"
    :checkbox-selector="$checkboxSelector ?? '.bulk-checkbox'"
    :restore-route="$restoreRoute ?? null"
    :delete-route="$deleteRoute ?? null"
    :action-route="$actionRoute ?? null"
    :unban-route="$unbanRoute ?? null"
    :noun="$noun ?? 'items'"
/>
