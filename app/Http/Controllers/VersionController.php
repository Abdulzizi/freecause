<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class VersionController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $path = (string) config('app.version_file');
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        return response()->json([
            'commit' => $data['commit'] ?? null,
            'release' => $data['release'] ?? $data['version'] ?? null,
            'deployed_at' => $data['deployed_at'] ?? null,
        ]);
    }
}
