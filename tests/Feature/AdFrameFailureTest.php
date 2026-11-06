<?php

namespace Tests\Feature;

use App\Support\Ads;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdFrameFailureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        config(['ads.enabled' => true, 'ads.frame_origin' => 'https://ads.freecause.com']);
    }

    public function test_frame_urls_bypass_old_cached_permanent_redirects(): void
    {
        $this->assertMatchesRegularExpression('#^https://ads\.freecause\.com/ads/frame/leaderboard\?v=[0-9a-f]{8}$#', Ads::frameUrl('leaderboard'));
    }

    public function test_wrong_host_frame_failure_is_empty_and_cannot_recurse(): void
    {
        $response = $this->get('https://www.freecause.com/ads/frame/rectangle')->assertNotFound();
        $this->assertSame('', $response->getContent());
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_unknown_slot_on_ad_host_is_empty(): void
    {
        $response = $this->get('https://ads.freecause.com/ads/frame/missing')->assertNotFound();
        $this->assertSame('', $response->getContent());
    }

    public function test_disabled_ads_return_empty_error_instead_of_site_layout(): void
    {
        config(['ads.enabled' => false]);
        $response = $this->get('https://ads.freecause.com/ads/frame/rectangle')->assertNotFound();
        $this->assertSame('', $response->getContent());
    }

    public function test_ad_host_cannot_render_a_recursive_site_error(): void
    {
        $response = $this->get('https://ads.freecause.com/en')->assertNotFound();
        $this->assertSame('', $response->getContent());
    }

    public function test_valid_versioned_frame_still_serves_ad_code_without_analytics(): void
    {
        $response = $this->get(Ads::frameUrl('rectangle'))->assertOk();
        $response->assertSee('atOptions', false)->assertDontSee('googletagmanager.com', false);
    }

    public function test_public_404_keeps_graphical_recovery_ads_and_real_error_status(): void
    {
        $response = $this->get('/en/petition/missing/999999999')->assertNotFound();
        $response->assertSee('id="fc-404-recovery"', false)
            ->assertSee(Ads::frameUrl('rectangle'), false)
            ->assertSee('/en/petitions', false);
        $this->assertStringContainsString('noindex', $response->getContent());
    }
}
