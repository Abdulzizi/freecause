<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Support\Ads;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetitionAdSlotsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    private function publishedPetition(): array
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        $slug = 'ad-slots-'.$petition->id;

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Ad Slots Petition',
            'slug' => $slug,
            'description' => 'Description',
        ]);

        return [$petition, $slug];
    }

    public function test_all_six_ad_slots_render_on_the_petition_page(): void
    {
        config(['ads.enabled' => true]);

        [$petition, $slug] = $this->publishedPetition();

        $html = $this->get("/en/petition/{$slug}/{$petition->id}")
            ->assertOk()
            ->getContent();

        foreach (['leaderboard', 'rectangle', 'skyscraper', 'banner468', 'native', 'mobile'] as $slot) {
            $this->assertStringContainsString("fc-ad fc-ad--{$slot}", $html, "missing slot: {$slot}");
        }
    }

    public function test_configured_slots_point_at_their_frame_but_empty_key_does_not(): void
    {
        config(['ads.enabled' => true]);
        // force one slot's key empty to prove the box-only (no frame) path
        config(['ads.slots.skyscraper.key' => '']);

        [$petition, $slug] = $this->publishedPetition();

        $html = $this->get("/en/petition/{$slug}/{$petition->id}")
            ->assertOk()
            ->getContent();

        // configured slots render an isolated ad frame
        $this->assertStringContainsString('/ads/frame/rectangle', $html);
        $this->assertStringContainsString('/ads/frame/native', $html);

        preg_match('/<iframe[^>]*\/ads\/frame\/rectangle[^>]*>/', $html, $rectangleFrame);
        $this->assertNotEmpty($rectangleFrame, 'missing rectangle frame');
        $this->assertStringContainsString('sandbox="allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox allow-forms allow-top-navigation-by-user-activation"', $rectangleFrame[0]);

        preg_match('/<iframe[^>]*\/ads\/frame\/native[^>]*>/', $html, $nativeFrame);
        $this->assertNotEmpty($nativeFrame, 'missing native frame');
        $this->assertStringContainsString('sandbox="allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox allow-forms allow-top-navigation-by-user-activation"', $nativeFrame[0]);

        // an empty key renders nothing: no box, no frame
        $this->assertStringNotContainsString('fc-ad--skyscraper', $html);
        $this->assertStringNotContainsString('/ads/frame/skyscraper', $html);
    }

    public function test_ad_frame_serves_the_loader_with_a_relaxed_csp(): void
    {
        config(['ads.enabled' => true]);

        $response = $this->get('/ads/frame/rectangle')->assertOk();

        // must stay frameable by our own pages, never by other origins
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');

        $body = $response->getContent();
        $this->assertStringContainsString('/ads/config/rectangle', $body);
        $this->assertStringContainsString(config('ads.slots.rectangle.key'), $body);

        // the frame needs inline/eval because the network loader injects inline script;
        // the public page CSP (tested in SecurityHeadersAdsenseTest) keeps forbidding it
        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline' 'unsafe-eval'", $csp);
    }

    public function test_ad_frame_404s_for_unknown_or_keyless_slot(): void
    {
        config(['ads.enabled' => true]);
        config(['ads.slots.skyscraper.key' => '']);

        $this->get('/ads/frame/doesnotexist')->assertNotFound();
        $this->get('/ads/frame/skyscraper')->assertNotFound();
    }

    public function test_master_flag_disables_every_slot(): void
    {
        config(['ads.enabled' => false]);

        [$petition, $slug] = $this->publishedPetition();

        $html = $this->get("/en/petition/{$slug}/{$petition->id}")
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('fc-ad fc-ad--', $html);
        $this->assertStringNotContainsString(config('ads.slots.rectangle.key'), $html);
    }

    public function test_slots_render_identically_on_a_second_locale(): void
    {
        config(['ads.enabled' => true]);

        // give the petition its own it translation so /it/ serves (not redirects to en)
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        $slug = 'ad-slots-it-'.$petition->id;
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'it',
            'title' => 'Petizione',
            'slug' => $slug,
            'description' => 'Descrizione',
        ]);

        // template is shared across locales; one implementation must cover every route
        $html = $this->get("/it/petition/{$slug}/{$petition->id}")
            ->assertOk()
            ->getContent();

        foreach (['leaderboard', 'rectangle', 'skyscraper', 'banner468', 'native', 'mobile'] as $slot) {
            $this->assertStringContainsString("fc-ad fc-ad--{$slot}", $html, "missing slot on it locale: {$slot}");
        }
    }

    public function test_pick_rotates_through_keyed_unrendered_slots(): void
    {
        config(['ads.enabled' => true]);
        config([
            'ads.slots.banner468.key' => 'k1',
            'ads.slots.rectangle.key' => 'k2',
            'ads.slots.native.key' => 'k3',
        ]);

        $this->assertSame('banner468', Ads::pick('infeed'));
        $this->assertSame('rectangle', Ads::pick('infeed'));
        $this->assertSame('native', Ads::pick('infeed'));
        $this->assertSame('banner468', Ads::pick('infeed'));
    }

    public function test_pick_skips_rendered_and_keyless_slots(): void
    {
        config(['ads.enabled' => true]);
        config([
            'ads.slots.banner468.key' => 'k1',
            'ads.slots.rectangle.key' => '',
            'ads.slots.native.key' => 'k3',
        ]);
        Ads::renderOnce('banner468');

        $this->assertSame('native', Ads::pick('infeed'));

        Ads::renderOnce('native');

        $this->assertNull(Ads::pick('infeed'));
        $this->assertNull(Ads::pick('unknown'));
    }
}
