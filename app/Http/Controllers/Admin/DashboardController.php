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

    private function getStats(): array
    {
        $todayStart = today()->startOfDay();
        $todayEnd = today()->endOfDay();
        $weekAgo = now()->subWeek();

        return [
            'users_total'        => $this->approxTableRows('users'),
            'users_verified'     => Cache::remember('admin:stats:users_verified', 3600, fn () => User::where('verified', 1)->count()),
            'users_new_today'    => Cache::remember('admin:stats:users_today', 300, fn () use ($todayStart, $todayEnd) => User::whereBetween('created_at', [$todayStart, $todayEnd])->count()),
            'users_new_week'     => Cache::remember('admin:stats:users_week', 300, fn () use ($weekAgo) => User::where('created_at', '>=', $weekAgo)->count()),

            'petitions_total'    => $this->approxTableRows('petitions'),
            'petitions_published'=> Cache::remember('admin:stats:petitions_published', 600, fn () => Petition::where('status', 'published')->count()),
            'petitions_draft'    => Cache::remember('admin:stats:petitions_draft', 600, fn () => Petition::where('status', 'draft')->count()),
            'petitions_pending'  => Cache::remember('admin:stats:petitions_pending', 600, fn () => Petition::where('status', 'pending')->count()),
            'petitions_new_today'=> Cache::remember('admin:stats:petitions_today', 300, fn () use ($todayStart, $todayEnd) => Petition::whereBetween('created_at', [$todayStart, $todayEnd])->count()),

            'signatures_total'   => $this->approxTableRows('signatures'),
            'signatures_today'   => Cache::remember('admin:stats:sigs_today', 300, fn () use ($todayStart, $todayEnd) => Signature::whereBetween('created_at', [$todayStart, $todayEnd])->count()),
            'signatures_week'    => Cache::remember('admin:stats:sigs_week', 300, fn () use ($weekAgo) => Signature::where('created_at', '>=', $weekAgo)->count()),

            'languages_active'   => Cache::remember('admin:stats:languages', 3600, fn () => Language::where('is_active', 1)->count()),
        ];
    }

    private function getQuickStats()
    {
        return Cache::remember('admin:dashboard:quick', 300, function () {
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
        return Cache::remember('admin:dashboard:recent', 120, function () {
            $recentUsers = User::orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'email', 'name', 'created_at', 'verified', 'level_id']);

            $recentPetitions = Petition::with('user:id,email')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'status', 'created_at', 'user_id', 'signature_count']);

            return [
                'users'     => $recentUsers,
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

        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'database_type' => config('database.default'),
            'database_size' => $dbSize ? "{$dbSize} MB" : 'N/A',
            'disk_free_space' => round(disk_free_space('.') / 1024 / 1024 / 1024, 2).' GB',
        ];
    }
}
