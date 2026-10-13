<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && ! $user->hasCompleteProfile()) {
            $return = $request->isMethod('GET') ? $request->fullUrl() : url()->previous();
            $profileUrl = lroute('profile');

            if (is_string($return) && $return !== '') {
                $profileUrl .= (str_contains($profileUrl, '?') ? '&' : '?').http_build_query(['return' => $return]);
            }

            return redirect()
                ->to($profileUrl)
                ->with('profile_required', 1);
        }

        return $next($request);
    }
}
