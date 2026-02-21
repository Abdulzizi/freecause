<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Permission;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $module, $action)
    {
        $user = auth()->user();
        logger("Checking permission: {$module} / {$action} for level {$user->level_id}");

        if (!$user) {
            abort(403);
        }

        if ($user->level->is_system ?? false) {
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
