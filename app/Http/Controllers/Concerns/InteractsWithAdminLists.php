<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait InteractsWithAdminLists
{
    protected function adminBulkIds(Request $request, string $table): array
    {
        $validator = validator(
            ['ids' => $request->input('ids', [])],
            [
                'ids' => ['required', 'array'],
                'ids.*' => ['integer', 'exists:'.$table.',id'],
            ]
        );

        if ($validator->fails()) {
            return [];
        }

        return array_values(array_map('intval', $validator->validated()['ids']));
    }
}
