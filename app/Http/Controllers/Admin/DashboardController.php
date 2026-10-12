<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Petition;
use App\Models\Signature;
use App\Models\User;
use App\Support\ApproxRows;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    use ApproxRows;

    public function index()
    {
        $stats = $this->getStats();
        $recentActivity = $this->getRecentActivity();
        $topPetitions = $this->getTopPetitions();
        $systemHealth = $this->getSystemHealth();
        $quickStats = $this->getQuickStats();

        return view('admin.dashboard', compact(
            'stats',
            'recentActivity',
            'topPetitions',
            'systemHealth',
            'quickStats'
        ));
    }

    public function warm(): void
    {
        $this->getStats();
        $this->getRecentActivity();
        $this->getTopPetitions();
        $this->getSystemHealth();
        $this->getQuickStats();
    }

    private function getStats(): array
    {
        return Cache::remember('admin:dashboard:stats:v3', 900, function () {
            $todayStart = today()->startOfDay();
            $todayEnd = today()->endOfDay();
            $weekAgo = now()->subWeek();

            $newUsers = User::query()
                ->selectRaw(
                    'SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as today, '.
                    'SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as week',
                    [$todayStart, $todayEnd, $weekAgo]
                )
                ->first();

            $petitions = Cache::remember('admin:petitions:status_counts:v2', 900, fn () => Petition::query()
                ->selectRaw(
                    'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as published, '.
                    'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as draft, '.
                    'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending, '.
                    'SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as new_today',
                    ['published', 'draft', 'pending', $todayStart, $todayEnd]
                )
                ->first());

            $newSignatures = Signature::query()
                ->selectRaw(
                    'SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as today, '.
                    'SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as week',
                    [$todayStart, $todayEnd, $weekAgo]
                )
                ->first();

            return [
                'users_total' => $this->approxTableRows('users'),
                // Exact verified COUNT() scans ~15M index entries on prod;
                // cache it for an hour instead of the block TTL.
                'users_verified' => Cache::remember('admin:users:verified_count', 3600, fn () => User::where('verified', 1)->count()),
                'users_new_today' => (int) $newUsers->today,
                'users_new_week' => (int) $newUsers->week,

                'petitions_total' => $this->approxTableRows('petitions'),
                'petitions_published' => (int) $petitions->published,
                'petitions_draft' => (int) $petitions->draft,
                'petitions_pending' => (int) $petitions->pending,
                'petitions_new_today' => (int) $petitions->new_today,

                'signatures_total' => $this->approxTableRows('signatures'),
                'signatures_today' => (int) $newSignatures->today,
                'signatures_week' => (int) $newSignatures->week,

                'languages_active' => Language::where('is_active', 1)->count(),
            ];
        });
    }

    private function getQuickStats()
    {
        return Cache::remember('admin:dashboard:quick', 900, function () {
            return [
                'logs_count' => $this->approxTableRows('logs'),
                'banned_ips' => $this->approxTableRows('banned_ips'),
                'categories' => $this->approxTableRows('categories'),
                'pages' => $this->approxTableRows('pages'),
            ];
        });
    }

    private function getRecentActivity(): array
    {
        return Cache::remember('admin:dashboard:recent', 300, function () {
            $recentUsers = User::orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'email', 'name', 'created_at', 'verified', 'level_id']);

            $recentPetitions = Petition::with('user:id,email')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'status', 'created_at', 'user_id', 'signature_count']);

            return [
                'users' => $recentUsers,
                'petitions' => $recentPetitions,
            ];
        });
    }

    private function getTopPetitions()
    {
        return Cache::remember('admin:dashboard:top_petitions', 300, fn () => Petition::where('status', 'published')
            ->orderByDesc('signature_count')
            ->limit(5)
            ->get(['id', 'signature_count', 'goal_signatures', 'created_at'])
        );
    }

    private function getSystemHealth(): array
    {
        return Cache::remember('admin:dashboard:health', 900, function () {
            $dbSize = 0;
            try {
                if (config('database.default') === 'sqlite') {
                    $dbPath = database_path('database.sqlite');
                    if (file_exists($dbPath)) {
                        $dbSize = round(filesize($dbPath) / 1024 / 1024, 2);
                    }
                }
            } catch (\Throwable) {
            }

            $diskFree = 'N/A';
            try {
                $free = disk_free_space('.');
                if ($free !== false) {
                    $diskFree = round($free / 1024 / 1024 / 1024, 2).' GB';
                }
            } catch (\Throwable) {
            }

            return [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'cache_driver' => config('cache.default'),
                'queue_driver' => config('queue.default'),
                'database_type' => config('database.default'),
                'database_size' => $dbSize ? "{$dbSize} MB" : 'N/A',
                'disk_free_space' => $diskFree,
            ];
        });
    }
}
