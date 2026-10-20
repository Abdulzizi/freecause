<?php

namespace Tests\Feature;

use App\Http\Middleware\AddSecurityHeaders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SecurityHeadersAdsenseTest extends TestCase
{
    use RefreshDatabase;

    private function cspFor(string $path): string
    {
        $request = Request::create($path, 'GET');
        $response = (new AddSecurityHeaders)->handle(
            $request,
            fn () => response('<html></html>', 200, ['Content-Type' => 'text/html'])
        );

        return (string) $response->headers->get('Content-Security-Policy');
    }

    public function test_public_csp_allows_adsense_script_source(): void
    {
        $csp = $this->cspFor('/en');

        $this->assertStringContainsString('pagead2.googlesyndication.com', $csp);
        $this->assertStringContainsString('googleads.g.doubleclick.net', $csp);
    }

    public function test_public_csp_allows_adsense_frames_and_beacons(): void
    {
        $csp = $this->cspFor('/en');

        $this->assertStringContainsString('frame-src', $csp);
        $this->assertStringContainsString('tpc.googlesyndication.com', $csp);
        $this->assertStringContainsString('connect-src', $csp);
        $this->assertStringContainsString('adtrafficquality.google', $csp);
    }

    public function test_object_src_none_still_enforced(): void
    {
        $csp = $this->cspFor('/en');

        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
    }

    public function test_admin_csp_allows_unsafe_eval_for_alpine(): void
    {
        $csp = $this->cspFor('/admin/login');

        $this->assertStringContainsString("script-src 'self' 'unsafe-inline' 'unsafe-eval' ", $csp);
        $this->assertStringContainsString('cdn.ckeditor.com', $csp);
    }

    public function test_public_csp_disallows_unsafe_eval(): void
    {
        $csp = $this->cspFor('/en');

        $this->assertStringNotContainsString('unsafe-eval', $csp);
    }

    public function test_public_csp_allows_adsterra_network(): void
    {
        $csp = $this->cspFor('/en/petition/x/1');

        $this->assertStringContainsString('https://bicea.org', $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertMatchesRegularExpression('/connect-src[^;]*https:\/\/bicea\.org/', $csp);
    }

    public function test_public_csp_allows_cloudflare_web_analytics_beacon(): void
    {
        $csp = $this->cspFor('/en');

        $this->assertStringContainsString('https://static.cloudflareinsights.com', $csp);
        $this->assertMatchesRegularExpression('/connect-src[^;]*https:\/\/cloudflareinsights\.com/', $csp);
    }

    public function test_ad_slot_config_endpoint_serves_atoptions_javascript(): void
    {
        $response = $this->get('/ads/config/rectangle');

        $response->assertOk();
        $this->assertStringContainsString('application/javascript', (string) $response->headers->get('Content-Type'));

        $body = (string) $response->getContent();
        $this->assertStringContainsString('atOptions', $body);
        $this->assertStringContainsString((string) config('ads.slots.rectangle.key'), $body);
        $this->assertStringContainsString("'height': 250", $body);

        $this->get('/ads/config/doesnotexist')->assertNotFound();
        $this->get('/ads/config/native')->assertNotFound();
        $this->get('/ads/config/banner468')->assertOk();
    }
}
