<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Closure;
use Illuminate\Support\Facades\Cache;

class CheckPermission
{
    public function handle($request, Closure $next, $module, $action)
    {
        $user = admin_user();

        if (! $user) {
            abort(403);
        }

        $user->load('level');

        if (! $user->level_id) {
            abort(403, 'Your account has no role assigned. Contact a system administrator.');
        }

        if ($user->level && $user->level->is_system) {
            return $next($request);
        }

        $cacheKey = "permissions:{$user->level_id}:{$module}:{$action}";

        $hasPermission = Cache::remember($cacheKey, 300, function () use ($user, $module, $action) {
            return Permission::where('level_id', $user->level_id)
                ->where('module', $module)
                ->where('action', $action)
                ->exists();
        });

        if (! $hasPermission) {
            abort(403);
        }

        return $next($request);
    }
}
