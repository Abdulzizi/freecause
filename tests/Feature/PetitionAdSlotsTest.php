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

    public function test_native_and_banner_slots_hide_on_mobile(): void
    {
        config(['ads.enabled' => true]);

        [$petition, $slug] = $this->publishedPetition();

        $html = $this->get("/en/petition/{$slug}/{$petition->id}")
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/class="[^"]*fc-ad--native[^"]*max-md:hidden/', $html);
        $this->assertMatchesRegularExpression('/class="[^"]*fc-ad--banner468[^"]*max-md:hidden/', $html);
        $this->assertDoesNotMatchRegularExpression('/class="[^"]*fc-ad--rectangle[^"]*max-(?:md|sm):hidden/', $html);
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
        $this->assertStringContainsString('src="https://ads.freecause.com/ads/frame/rectangle?v=2"', $rectangleFrame[0]);
        $this->assertStringContainsString('sandbox="allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox allow-forms"', $rectangleFrame[0]);

        preg_match('/<iframe[^>]*\/ads\/frame\/native[^>]*>/', $html, $nativeFrame);
        $this->assertNotEmpty($nativeFrame, 'missing native frame');
        $this->assertStringContainsString('sandbox="allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox allow-forms"', $nativeFrame[0]);

        // an empty key renders nothing: no box, no frame
        $this->assertStringNotContainsString('fc-ad--skyscraper', $html);
        $this->assertStringNotContainsString('/ads/frame/skyscraper', $html);
    }

    public function test_parent_origin_prefers_the_canonical_url(): void
    {
        config(['app.canonical_url' => 'https://www.freecause.com:8443', 'app.url' => 'http://fallback.test']);

        $this->assertSame('https://www.freecause.com:8443', Ads::parentOrigin());
    }

    public function test_parent_origin_falls_back_to_app_url(): void
    {
        config(['app.url' => 'https://freecause.com']);
        config(['app.canonical_url' => '']);

        $this->assertSame('https://freecause.com', Ads::parentOrigin());

        config(['app.canonical_url' => 'not-a-url']);

        $this->assertSame('https://freecause.com', Ads::parentOrigin());
    }

    public function test_parent_origin_is_empty_when_both_urls_lack_a_host(): void
    {
        config(['app.canonical_url' => '', 'app.url' => 'not-a-url']);

        $this->assertSame('', Ads::parentOrigin());
    }

    public function test_ad_frame_serves_the_loader_with_a_relaxed_csp(): void
    {
        config(['ads.enabled' => true]);

        $response = $this->get('https://ads.freecause.com/ads/frame/rectangle')->assertOk();

        // the ads origin serves only the frame; the site must be able to embed it,
        // so X-Frame-Options is withheld (frame-ancestors in the CSP does the gating)
        $response->assertHeaderMissing('X-Frame-Options');

        $body = $response->getContent();
        $this->assertStringContainsString('atOptions', $body);
        $this->assertStringContainsString(config('ads.slots.rectangle.key'), $body);
        $this->assertStringContainsString('src="'.config('ads.host').'/22/', $body);

        // the frame needs inline because the network loader injects inline script;
        // the public page CSP (tested in SecurityHeadersAdsenseTest) keeps forbidding it
        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline' 'unsafe-eval'", $csp);
        $this->assertDoesNotMatchRegularExpression('/[\s;]http:[\s;]/', $csp);
        $this->assertStringContainsString('frame-ancestors '.config('app.canonical_url'), $csp);
        $this->assertStringNotContainsString((string) config('app.url'), $csp);
    }

    public function test_ad_frame_404s_for_unknown_or_keyless_slot(): void
    {
        config(['ads.enabled' => true]);
        config(['ads.slots.skyscraper.key' => '']);

        $this->get('https://ads.freecause.com/ads/frame/doesnotexist')->assertNotFound();
        $this->get('https://ads.freecause.com/ads/frame/skyscraper')->assertNotFound();
    }

    public function test_frame_served_only_on_frame_origin_and_page_leaks_no_zone_key(): void
    {
        config(['ads.enabled' => true]);

        // NB: relative requests must precede the absolute ads-origin request --
        // MakesHttpRequests::prepareUrlForRequest resolves them via url(), whose
        // root sticks at the last request's host.
        $this->get('/ads/frame/rectangle')->assertNotFound();

        [$petition, $slug] = $this->publishedPetition();
        $html = $this->get("/en/petition/{$slug}/{$petition->id}")->assertOk()->getContent();

        $this->assertStringNotContainsString('data-ad-id', $html);

        foreach (array_keys(config('ads.slots')) as $name) {
            $cfg = Ads::slot($name);
            if ($cfg && ! empty($cfg['key'])) {
                $this->assertStringNotContainsString($cfg['key'], $html, "zone key leaked for slot: {$name}");
            }
        }

        $frame = $this->get('https://ads.freecause.com/ads/frame/rectangle')->assertOk();
        $this->assertStringContainsString('src="'.config('ads.host').'/22/', (string) $frame->getContent());
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

    public function test_anchor_bar_is_absent_when_ads_are_disabled(): void
    {
        config(['ads.enabled' => false]);

        [$petition, $slug] = $this->publishedPetition();

        $html = $this->get("/en/petition/{$slug}/{$petition->id}")
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('id="fc-ad-anchor"', $html);
    }

    public function test_anchor_bar_renders_when_ads_are_enabled_with_a_valid_origin(): void
    {
        config(['ads.enabled' => true]);
        config(['ads.frame_origin' => 'https://ads.freecause.com']);

        [$petition, $slug] = $this->publishedPetition();

        $html = $this->get("/en/petition/{$slug}/{$petition->id}")
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="fc-ad-anchor"', $html);
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
