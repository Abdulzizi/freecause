<?php

namespace Tests\Feature;

use App\Support\Ads;
use App\Support\Settings;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdsWwwOriginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        config(['app.canonical_url' => 'https://www.freecause.com', 'ads.enabled' => true]);
        Settings::set('ads_frame_origin', 'https://www.freecause.com', 'global');
    }

    public function test_www_frame_origin_does_not_block_public_homepage_and_preserves_sandbox(): void
    {
        $response = $this->get('https://www.freecause.com/en')->assertOk();
        preg_match('/<iframe[^>]*src="https:\/\/www\.freecause\.com\/ads\/frame\/leaderboard\?v=2"[^>]*>/', $response->getContent(), $frame);
        $this->assertNotEmpty($frame);
        $this->assertStringNotContainsString('sandbox', (string) $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('allow-scripts', $frame[0]);
        $this->assertStringNotContainsString('allow-same-origin', $frame[0]);
    }

    public function test_frame_is_served_on_www_without_loading_site_analytics(): void
    {
        $this->assertSame('https://www.freecause.com/ads/frame/rectangle?v=2', Ads::frameUrl('rectangle'));
        $this->get(Ads::frameUrl('rectangle'))->assertOk()
            ->assertSee('atOptions', false)->assertDontSee('googletagmanager.com', false);
    }

    public function test_public_missing_pages_still_render_graphical_404_and_ads(): void
    {
        $this->get('https://www.freecause.com/en/petition/missing/999999999')->assertNotFound()
            ->assertSee('id="fc-404-recovery"', false)
            ->assertSee('https://www.freecause.com/ads/frame/rectangle?v=2', false);
    }

    public function test_www_frame_errors_are_empty_and_cannot_recurse(): void
    {
        $response = $this->get('https://www.freecause.com/ads/frame/unknown')->assertNotFound();
        $this->assertSame('', $response->getContent());
    }

    public function test_old_ad_frame_redirects_to_www_and_preserves_query(): void
    {
        $this->get('https://ads.freecause.com/ads/frame/rectangle?v=2')->assertStatus(301)
            ->assertRedirect('https://www.freecause.com/ads/frame/rectangle?v=2');
    }

    public function test_old_ad_host_does_not_serve_duplicate_site_pages(): void
    {
        $this->get('https://ads.freecause.com/en')->assertStatus(301)
            ->assertRedirect('https://www.freecause.com/en');
    }

    public function test_www_frame_response_enforces_sandbox_even_when_opened_directly(): void
    {
        $response = $this->get(Ads::frameUrl('rectangle'))->assertOk();
        $policy = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('sandbox allow-scripts allow-popups allow-popups-to-escape-sandbox allow-forms;', $policy);
        $this->assertStringNotContainsString('allow-same-origin', $policy);
    }

    public function test_legacy_dedicated_origin_does_not_inherit_www_sandbox(): void
    {
        Settings::set('ads_frame_origin', 'https://ads.freecause.com', 'global');
        $response = $this->get(Ads::frameUrl('rectangle'))->assertOk();
        $this->assertStringNotContainsString('sandbox', (string) $response->headers->get('Content-Security-Policy'));
    }
}
