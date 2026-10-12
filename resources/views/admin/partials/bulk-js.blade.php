
@include('admin.partials.bulk-module', ['bulkConfig' => [
    'actionRoute' => $actionRoute ?? null,
    'banRoute' => $banRoute ?? null,
    'deleteRoute' => $deleteRoute ?? null,
    'unbanRoute' => $unbanRoute ?? null,
    'restoreRoute' => $restoreRoute ?? null,
    'checkboxSelector' => $checkboxSelector ?? '.bulk-checkbox',
    'noun' => $noun ?? 'items',
    'emptyMsg' => $emptyMsg ?? 'No items selected',
    'csrfToken' => csrf_token(),
]])
