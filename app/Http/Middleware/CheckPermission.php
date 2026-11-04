<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
    public function handle($request, Closure $next, $module, $action)
    {
        $user = admin_user();

        if (! $user) {
            abort(403);
        }

        if (! $user->level_id) {
            abort(403, 'Your account has no role assigned. Contact a system administrator.');
        }

        if (! admin_can($module, $action)) {
            abort(403);
        }

        return $next($request);
    }
}
