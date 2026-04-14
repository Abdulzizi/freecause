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

        return view('admin.dashboard', compact(
            'stats',
            'recentActivity',
            'topPetitions',
            'systemHealth'
        ));
    }

    private function getStats(): array
    {
        $cacheKey = 'admin:dashboard:stats';

        return Cache::remember($cacheKey, 300, function () {
            return [
                'users_total' => $this->approxTableRows('users'),
                'users_verified' => Cache::remember('admin:stats:users_verified', 600, fn () => User::where('verified', 1)->count()
                ),
                'users_new_today' => User::whereDate('created_at', today())->count(),
                'users_new_week' => User::where('created_at', '>=', now()->subWeek())->count(),

                'petitions_total' => $this->approxTableRows('petitions'),
                'petitions_published' => Cache::remember('admin:stats:petitions_published', 600, fn () => Petition::where('status', 'published')->count()
                ),
                'petitions_draft' => Petition::where('status', 'draft')->count(),
                'petitions_pending' => Petition::where('status', 'pending')->count(),
                'petitions_new_today' => Petition::whereDate('created_at', today())->count(),

                'signatures_total' => $this->approxTableRows('signatures'),
                'signatures_today' => Signature::whereDate('created_at', today())->count(),
                'signatures_week' => Signature::where('created_at', '>=', now()->subWeek())->count(),

                'languages_active' => Language::where('is_active', 1)->count(),
            ];
        });
    }

    private function getRecentActivity(): array
    {
        $recentUsers = User::orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'email', 'name', 'created_at', 'verified', 'level_id']);

        $recentPetitions = Petition::with('user:id,email')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'title', 'status', 'created_at', 'user_id', 'signature_count']);

        return [
            'users' => $recentUsers,
            'petitions' => $recentPetitions,
        ];
    }

    private function getTopPetitions(): array
    {
        return Petition::where('status', 'published')
            ->orderByDesc('signature_count')
            ->limit(5)
            ->get(['id', 'title', 'signature_count', 'goal', 'created_at']);
    }

    private function getSystemHealth(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'database_type' => config('database.default'),
            'disk_free_space' => round(disk_free_space('.') / 1024 / 1024 / 1024, 2).' GB',
        ];
    }
}
