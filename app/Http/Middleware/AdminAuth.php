<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        if (! admin_user()?->canAccessAdmin() || admin_user()?->isBanned()) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();

            abort(403, 'Your account no longer has admin access.');
        }

        $hash = $request->session()->get('admin_password_hash');

        if ($hash === null) {
            $request->session()->put('admin_password_hash', admin_user()->password);
        } elseif (! hash_equals((string) $hash, (string) admin_user()->password)) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();

            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
