<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class VersionController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $path = storage_path('app/version.json');
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        return response()->json([
            'commit' => $data['commit'] ?? null,
            'release' => $data['release'] ?? $data['version'] ?? null,
            'deployed_at' => $data['deployed_at'] ?? null,
        ]);
    }
}
