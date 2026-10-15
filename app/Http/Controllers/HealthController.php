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

        // Each sub-check returns a 'healthy' bool; the probe is healthy only
        // when every one of them is true. (Previously read a key the
        // sub-checks never set, so /up always returned 200.)
        $healthy = ! in_array(false, array_column($checks, 'healthy'), true);

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

            return ['healthy' => true];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
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

            return ['healthy' => true];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'error' => 'Cache operation failed',
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $dir = storage_path('framework/cache');

            if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
                throw new \Exception('Storage directory not writable');
            }

            $probe = $dir.'/.health-'.getmypid();
            if (file_put_contents($probe, '1') === false) {
                throw new \Exception('Storage write failed');
            }
            @unlink($probe);

            return ['healthy' => true];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'error' => 'Storage write failed',
            ];
        }
    }
}
