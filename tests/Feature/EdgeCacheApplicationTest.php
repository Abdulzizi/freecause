<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EdgeCacheApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
    }

    public function test_real_anonymous_listings_bypass_in_every_active_locale(): void
    {
        foreach (Language::where('is_active', true)->pluck('code') as $locale) {
            $response = $this->get('/'.$locale.'/petitions')->assertOk();
            $response->assertHeader('X-FreeCause-Edge-Cache', 'bypass');
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
    }

    public function test_authenticated_listing_preserves_user_state_and_bypasses(): void
    {
        $user = User::factory()->create(['verified' => true]);
        $this->actingAs($user)->get('/en/petitions')
            ->assertOk()->assertHeader('X-FreeCause-Edge-Cache', 'bypass');
        $this->assertAuthenticatedAs($user);
    }

    public function test_root_language_detection_and_explicit_home_still_redirect(): void
    {
        $this->withHeaders(['Accept-Language' => 'fr'])->get('/')->assertRedirect('/fr');
        $this->withHeaders(['Accept-Language' => 'fr'])->get('/en')->assertRedirect('/fr');
    }
}
