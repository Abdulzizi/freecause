<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $healthy = ! in_array(false, array_column($checks, 'healthy'));

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');

            return ['database' => true];
        } catch (\Exception $e) {
            return [
                'database' => false,
                'error' => 'Database connection failed',
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'health_check_'.time();
            Cache::put($key, true, 10);

            if (Cache::get($key) !== true) {
                throw new \Exception('Cache read/write failed');
            }

            Cache::forget($key);

            return ['cache' => true];
        } catch (\Exception $e) {
            return [
                'cache' => false,
                'error' => 'Cache operation failed',
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $path = storage_path('framework/cache/.gitkeep');
            $dir = dirname($path);

            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            return ['storage' => true];
        } catch (\Exception $e) {
            return [
                'storage' => false,
                'error' => 'Storage write failed',
            ];
        }
    }
}
