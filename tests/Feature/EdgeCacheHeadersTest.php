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
    private function listingRequest(string $uri = '/en/petitions'): Request
    {
        $request = Request::create($uri);
        $route = new Route('GET', ltrim((string) parse_url($uri, PHP_URL_PATH), '/'), fn () => null);
        $route->name('petitions.index');
        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    public function test_stateless_render_is_eligible_with_ttl_capped_at_900(): void
    {
        config()->set('edge-cache.ttl', 3600);
        $request = $this->listingRequest();
        $request->attributes->set('stateless_render', true);
        $response = (new EdgeCacheHeaders)->handle($request, fn () => response('<html>Listing</html>', 200, ['Content-Type' => 'text/html']));

        $this->assertSame('eligible', $response->headers->get('X-FreeCause-Edge-Cache'));
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('s-maxage=900', $cacheControl);
        $this->assertStringNotContainsString('no-store', $cacheControl);
    }

    public function test_current_sessionful_html_bypasses(): void
    {
        $request = $this->listingRequest();
        $request->attributes->set('stateless_render', true);
        $store = new Store('test', new ArraySessionHandler(120));
        $store->start();
        $request->setLaravelSession($store);
        $response = (new EdgeCacheHeaders)->handle($request, fn () => response('<html>Listing</html>', 200, ['Content-Type' => 'text/html'])->cookie('test_session', 'retained'));

        $this->assertSame('bypass', $response->headers->get('X-FreeCause-Edge-Cache'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertCount(1, $response->headers->getCookies());
    }

    public function test_listing_without_stateless_marker_bypasses(): void
    {
        $request = $this->listingRequest();
        $response = (new EdgeCacheHeaders)->handle($request, fn () => response('<html>Listing</html>', 200, ['Content-Type' => 'text/html']));
        $this->assertSame('bypass', $response->headers->get('X-FreeCause-Edge-Cache'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_stateless_listing_with_request_cookie_or_query_bypasses(): void
    {
        foreach (['cookie', 'query'] as $case) {
            $request = $case === 'query' ? $this->listingRequest('/en/petitions?page=2') : $this->listingRequest();
            $request->attributes->set('stateless_render', true);
            if ($case === 'cookie') {
                $request->headers->set('Cookie', 'remember_web=opaque');
            }
            $response = (new EdgeCacheHeaders)->handle($request, fn () => response('<html>Listing</html>', 200, ['Content-Type' => 'text/html']));
            $this->assertSame('bypass', $response->headers->get('X-FreeCause-Edge-Cache'), $case);
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'), $case);
        }
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
