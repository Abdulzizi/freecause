<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\Ads;
use App\Support\Settings;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdsAdminConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    private function systemAdmin(): User
    {
        return User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);
    }

    private function optionsEditor(): User
    {
        $level = UserLevel::create([
            'name' => 'options-editor',
            'visible_name' => 'Options Editor',
            'is_system' => false,
        ]);
        Permission::create(['level_id' => $level->id, 'module' => 'options', 'action' => 'edit']);
        Permission::create(['level_id' => $level->id, 'module' => 'options', 'action' => 'view']);

        return User::factory()->create(['level_id' => $level->id, 'verified' => true]);
    }

    private function publishedPetition(): array
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        $slug = 'ads-admin-'.$petition->id;
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Ads Admin',
            'slug' => $slug,
            'description' => 'Description',
        ]);

        return [$petition, $slug];
    }

    public function test_resolver_prefers_settings_over_config(): void
    {
        config(['ads.enabled' => true, 'ads.slots.rectangle.key' => 'CONFIG_KEY']);

        // no setting -> config value
        $this->assertSame('CONFIG_KEY', Ads::slot('rectangle')['key']);

        // setting wins
        Settings::set('ads_slot_rectangle_key', 'DB_KEY', 'global');
        $this->assertSame('DB_KEY', Ads::slot('rectangle')['key']);

        // blank setting -> falls back to config
        Settings::set('ads_slot_rectangle_key', '', 'global');
        $this->assertSame('CONFIG_KEY', Ads::slot('rectangle')['key']);
    }

    public function test_resolver_master_flag_from_settings_overrides_config(): void
    {
        config(['ads.enabled' => true]);
        $this->assertTrue(Ads::enabled());

        Settings::set('ads_enabled', false, 'global');
        $this->assertFalse(Ads::enabled());
    }

    public function test_site_host_setting_falls_back_to_configured_frame_origin(): void
    {
        config([
            'app.canonical_url' => 'https://www.freecause.com',
            'ads.enabled' => true,
            'ads.frame_origin' => 'https://ads.freecause.com',
        ]);
        Settings::set('ads_frame_origin', 'https://www.freecause.com', 'global');

        $this->assertSame('https://ads.freecause.com', Ads::frameOrigin());

        [$petition, $slug] = $this->publishedPetition();
        $html = $this->get("/en/petition/{$slug}/{$petition->id}")->assertOk()->getContent();

        preg_match('/<iframe[^>]*src="https:\/\/ads\.freecause\.com\/ads\/frame\/rectangle\?v=2"[^>]*>/', $html, $frame);
        $this->assertNotEmpty($frame);
        $this->assertStringContainsString('allow-same-origin', $frame[0]);
    }

    public function test_site_host_frame_origin_is_rejected_from_config_and_aliases(): void
    {
        config([
            'app.canonical_url' => 'https://www.freecause.com',
            'ads.enabled' => true,
        ]);

        config(['ads.frame_origin' => 'https://www.freecause.com']);
        $this->assertSame('', Ads::frameOrigin());

        config(['ads.frame_origin' => 'https://ads.freecause.com']);
        Settings::set('ads_frame_origin', 'https://freecause.com', 'global');
        $this->assertSame('https://ads.freecause.com', Ads::frameOrigin());

        config(['ads.frame_origin' => 'https://freecause.com']);
        Settings::set('ads_frame_origin', '', 'global');
        $this->assertSame('', Ads::frameOrigin());

        [$petition, $slug] = $this->publishedPetition();
        $html = $this->get("/en/petition/{$slug}/{$petition->id}")->assertOk()->getContent();
        $this->assertStringNotContainsString('/ads/frame/', $html);
    }

    public function test_system_admin_can_save_ad_keys_and_they_render_on_the_petition_page(): void
    {
        config(['ads.enabled' => true]);

        $this->actingAs($this->systemAdmin(), 'admin')
            ->post(route('admin.options.global.update'), [
                'ads_enabled' => '1',
                'ads_slot_rectangle_key' => 'ADMIN_RECT_KEY',
            ])
            ->assertRedirect();

        $this->assertSame('ADMIN_RECT_KEY', Settings::get('ads_slot_rectangle_key', null, 'global'));

        [$petition, $slug] = $this->publishedPetition();
        $html = $this->get("/en/petition/{$slug}/{$petition->id}")->assertOk()->getContent();

        $this->assertStringContainsString('data-ad-slot="rectangle"', $html);
        $this->assertStringNotContainsString('ADMIN_RECT_KEY', $html);

        $frame = $this->get('https://ads.freecause.com/ads/frame/rectangle')->assertOk();
        $this->assertStringContainsString('ADMIN_RECT_KEY', (string) $frame->getContent());
    }

    public function test_non_system_admin_cannot_change_ad_keys(): void
    {
        Settings::set('ads_slot_rectangle_key', 'ORIGINAL', 'global');

        $this->actingAs($this->optionsEditor(), 'admin')
            ->post(route('admin.options.global.update'), [
                'ads_slot_rectangle_key' => 'HACKED',
            ])
            ->assertRedirect();

        // system-only key must be untouched by a non-system admin
        $this->assertSame('ORIGINAL', Settings::get('ads_slot_rectangle_key', null, 'global'));
    }

    public function test_only_six_slots_registered_and_placements_reference_them(): void
    {
        $expected = ['native', 'leaderboard', 'banner468', 'rectangle', 'mobile', 'skyscraper'];
        $this->assertSame($expected, array_keys(config('ads.slots')));

        foreach (config('ads.placements', []) as $placement => $pool) {
            foreach ((array) $pool as $slot) {
                $this->assertContains($slot, $expected, "$placement references unsupported slot: $slot");
            }
        }

        $this->assertNull(Ads::slot('mobile_feed'));
        $this->assertNull(Ads::slot('mobile_float'));
        $this->assertNull(Ads::slot('skyscraper_left'));
        $this->assertNull(Ads::pick('float'));

        $this->actingAs($this->systemAdmin(), 'admin')
            ->post(route('admin.options.global.update'), [
                'ads_slot_mobile_key' => 'MOBILE_KEY',
                'ads_slot_native_key' => 'NATIVE_KEY',
            ])
            ->assertRedirect();

        $this->assertSame('MOBILE_KEY', Settings::get('ads_slot_mobile_key', null, 'global'));
        $this->assertSame('MOBILE_KEY', Ads::slot('mobile')['key']);
        $this->assertSame('NATIVE_KEY', Ads::slot('native')['key']);
    }
}
