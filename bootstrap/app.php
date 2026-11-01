<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\AdminAuditLog;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AppendSpeculationRulesHeader;
use App\Http\Middleware\BlockBannedIp;
use App\Http\Middleware\BlockBannedUser;
use App\Http\Middleware\CanonicalHost;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\ConditionalLogging;
use App\Http\Middleware\DebugForIp;
use App\Http\Middleware\EdgeCacheHeaders;
use App\Http\Middleware\EnsureProfileComplete;
use App\Http\Middleware\LegacyWordRedirect;
use App\Http\Middleware\NoCacheHeaders;
use App\Http\Middleware\OptionalSession;
use App\Http\Middleware\SetLocale;
use App\Support\Ads;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->registered(function (Application $app) {
        $app->useLangPath(base_path('lang'));
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

        $middleware->web(append: [
            AuthenticateSession::class,
        ]);

        $middleware->alias([
            'setLocale' => SetLocale::class,
            'admin.auth' => AdminAuth::class,
            'admin.audit' => AdminAuditLog::class,
            'block.banned.ip' => BlockBannedIp::class,
            'block.banned.user' => BlockBannedUser::class,
            'permission' => CheckPermission::class,
            'no.cache' => NoCacheHeaders::class,
            'optional.session' => OptionalSession::class,
            'profile.complete' => EnsureProfileComplete::class,
        ]);

        $middleware->preventRequestsDuringMaintenance([
            'sitemap.xml',
            'sitemap-*.xml',
            'robots.txt',
        ]);

        $middleware->prepend(ConditionalLogging::class);

        $middleware->append(DebugForIp::class);
        $middleware->append(BlockBannedIp::class);
        $middleware->append(AddSecurityHeaders::class);
        $middleware->append(CanonicalHost::class);
        $middleware->append(LegacyWordRedirect::class);
        $middleware->append(EdgeCacheHeaders::class);
        $middleware->append(AppendSpeculationRulesHeader::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);

        // Ad-frame failures must never render the full site: its ad slots would
        // request more frames, recursively loading error pages and analytics.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($request->is('ads/frame/*') || Ads::frameHostMatchesRequest()) {
                return response('', $e->getStatusCode(), [
                    ...$e->getHeaders(),
                    'Cache-Control' => 'no-store',
                    'X-Robots-Tag' => 'noindex, nofollow',
                ]);
            }
        });

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

            toast('Too many attempts. Please slow down and try again in a minute.', 'warning');

            return back()->withErrors(['email' => 'Too many attempts. Please try again in a minute.']);
        });
    })->create();
