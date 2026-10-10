<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_is_publicly_cacheable_without_session_cookie(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertHeader('Cache-Control', 'max-age=300, public, s-maxage=86400, stale-while-revalidate=3600');
        $this->assertCount(0, $response->headers->getCookies());
        $response->assertSee('<sitemapindex', false);
    }

    public function test_sitemap_and_robots_bypass_maintenance_mode(): void
    {
        $except = app(PreventRequestsDuringMaintenance::class)->getExcludedPaths();

        $this->assertContains('sitemap.xml', $except);
        $this->assertContains('sitemap-*.xml', $except);
        $this->assertContains('robots.txt', $except);
    }
}
