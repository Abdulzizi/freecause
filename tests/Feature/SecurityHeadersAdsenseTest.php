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
        $this->assertStringContainsString('https://ads.freecause.com', $csp);
        $this->assertStringContainsString('connect-src', $csp);
        $this->assertStringContainsString('adtrafficquality.google', $csp);
    }

    public function test_public_csp_allows_youtube_nocookie_frames_only_on_the_public_site(): void
    {
        $this->assertMatchesRegularExpression('#frame-src [^;]*https://www\.youtube-nocookie\.com[ ;]#', $this->cspFor('/en'));
        $this->assertStringNotContainsString('youtube-nocookie.com', $this->cspFor('/admin/login'));
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

    public function test_public_csp_limits_script_cdns_to_quill(): void
    {
        preg_match('/script-src[^;]*/', $this->cspFor('/en'), $m);
        $scriptSrc = $m[0].' ';

        $this->assertStringNotContainsString('ajax.googleapis.com', $scriptSrc);
        $this->assertStringNotContainsString('cdnjs.cloudflare.com', $scriptSrc);
        $this->assertStringNotContainsString('https://cdn.jsdelivr.net ', $scriptSrc);
        $this->assertStringContainsString('https://cdn.jsdelivr.net/npm/quill@1.3.7/', $scriptSrc);
        $this->assertStringContainsString('https://cdnjs.cloudflare.com', $this->cspFor('/admin/login'));
    }
}
