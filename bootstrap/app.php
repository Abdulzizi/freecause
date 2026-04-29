<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\AdminAuditLog;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\BlockBannedIp;
use App\Http\Middleware\BlockBannedUser;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\DebugForIp;
use App\Http\Middleware\NoCacheHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->registered(function (Application $app) {
        $app->useLangPath(resource_path('lang'));
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $trustedProxies = env('TRUSTED_PROXIES');
        if ($trustedProxies) {
            $middleware->trustProxies(at: array_map('trim', explode(',', $trustedProxies)));
        } else {
            $middleware->trustProxies(at: []);
        }

        $middleware->alias([
            'setLocale' => SetLocale::class,
            'admin.auth' => AdminAuth::class,
            'admin.audit' => AdminAuditLog::class,
            'block.banned.ip' => BlockBannedIp::class,
            'block.banned.user' => BlockBannedUser::class,
            'permission' => CheckPermission::class,
            'no.cache' => NoCacheHeaders::class,
        ]);

        $middleware->append(DebugForIp::class);
        $middleware->append(BlockBannedIp::class);
        $middleware->append(AddSecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 401);
            }

            return redirect()->guest(lroute('login'));
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Too many attempts. Please try again later.',
                ], 429);
            }

            return back()->withErrors([
                'email' => 'Too many attempts. Please try again in a minute.',
            ]);
        });
    })->create();
