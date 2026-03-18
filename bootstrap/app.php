<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\BlockBannedIp;
use App\Http\Middleware\BlockBannedUser;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\DebugForIp;
use App\Http\Middleware\NoCacheHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'setLocale' => SetLocale::class,
            'admin.auth' => AdminAuth::class,
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
                    'message' => 'Too many attempts. Please try again later.'
                ], 429);
            }

            return back()->withErrors([
                'email' => 'Too many attempts. Please try again in a minute.'
            ]);
        });
    })->create();
