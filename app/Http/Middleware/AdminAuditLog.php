<?php

namespace App\Http\Middleware;

use App\Support\AppLog;
use Closure;
use Illuminate\Http\Request;

class AdminAuditLog
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log all state-changing admin requests
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        try {
            $user = admin_user();
            $adminId = $user?->id ?? 'unknown';
            $adminEmail = $user?->email ?? 'unknown';

            $path = $request->path();
            $method = $request->method();

            // Exclude password fields from logged input
            $input = $request->except(['password', 'password_confirmation', 'smtp_pass', 'google_client_secret']);
            // Truncate large inputs to keep logs manageable
            array_walk($input, function (&$v) {
                if (is_string($v) && mb_strlen($v) > 300) {
                    $v = mb_substr($v, 0, 300) . '…';
                }
            });

            $status = $response->getStatusCode();

            AppLog::info(
                "Admin action: {$method} /{$path}",
                "Admin #{$adminId} ({$adminEmail}) | Status: {$status} | Input: " . json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR),
                'admin.audit'
            );
        } catch (\Throwable) {
            // Never let audit logging break the response
        }

        return $response;
    }
}
