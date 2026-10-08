<?php

namespace Tests\Feature;

use App\Http\Middleware\EdgeCacheHeaders;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Tests\TestCase;

class EdgeCacheHeadersTest extends TestCase
{
    private function listingRequest(): Request
    {
        $request = Request::create('/en/petitions');
        $route = new Route('GET', 'en/petitions', fn () => null);
        $route->name('petitions.index');
        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    public function test_current_sessionful_html_bypasses(): void
    {
        $request = $this->listingRequest();
        $store = new Store('test', new ArraySessionHandler(120));
        $store->start();
        $request->setLaravelSession($store);
        $response = (new EdgeCacheHeaders)->handle($request, fn () => response('<html>Listing</html>', 200, ['Content-Type' => 'text/html'])->cookie('test_session', 'retained'));

        $this->assertSame('bypass', $response->headers->get('X-FreeCause-Edge-Cache'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertCount(1, $response->headers->getCookies());
    }

    public function test_listings_bypass(): void
    {
        $request = $this->listingRequest();
        $response = (new EdgeCacheHeaders)->handle($request, fn () => response('<html>Listing</html>', 200, ['Content-Type' => 'text/html']));
        $this->assertSame('bypass', $response->headers->get('X-FreeCause-Edge-Cache'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_cookies_authorization_queries_and_csrf_forms_bypass(): void
    {
        foreach (['cookie', 'authorization', 'query', 'csrf'] as $case) {
            $request = $this->listingRequest();
            if ($case === 'cookie') {
                $request->headers->set('Cookie', 'remember_web=opaque');
            } elseif ($case === 'authorization') {
                $request->headers->set('Authorization', 'Bearer placeholder');
            } elseif ($case === 'query') {
                $request->server->set('QUERY_STRING', 'page=2');
            }
            $content = $case === 'csrf' ? '<input name="_token" value="placeholder">' : '<html>Listing</html>';
            $response = (new EdgeCacheHeaders)->handle($request, fn () => response($content, 200, ['Content-Type' => 'text/html']));
            $this->assertSame('bypass', $response->headers->get('X-FreeCause-Edge-Cache'), $case);
        }
    }
}
