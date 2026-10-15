<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
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

    public function test_configured_slots_emit_their_loader_script_but_empty_key_does_not(): void
    {
        config(['ads.enabled' => true]);
        // force one slot's key empty to prove the box-only (no script) path
        config(['ads.slots.skyscraper.key' => '']);

        [$petition, $slug] = $this->publishedPetition();

        $html = $this->get("/en/petition/{$slug}/{$petition->id}")
            ->assertOk()
            ->getContent();

        // iframe banner (300x250) + native both load their key
        $this->assertStringContainsString(config('ads.slots.rectangle.key'), $html);
        $this->assertStringContainsString('container-'.config('ads.slots.native.key'), $html);

        // an empty key renders the reserved box but no script / no data-ad-id value
        $this->assertStringContainsString('fc-ad fc-ad--skyscraper', $html);
        $this->assertStringContainsString('data-ad-id=""', $html);
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
}
