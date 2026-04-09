<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Permission;

class CheckPermission
{
    public function handle($request, Closure $next, $module, $action)
    {
        $user = admin_user();

        if (!$user) {
            abort(403);
        }

        $user->load('level');

        if (!$user->level_id) {
            abort(403, 'Your account has no role assigned. Contact a system administrator.');
        }

        if ($user->level && $user->level->is_system) {
            return $next($request);
        }

        $hasPermission = Permission::where('level_id', $user->level_id)
            ->where('module', $module)
            ->where('action', $action)
            ->exists();

        if (!$hasPermission) {
            abort(403);
        }

        return $next($request);
    }
}
