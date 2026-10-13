<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetitionLocaleFallbackRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
    }

    private function petitionWithSpanishOnly(): Petition
    {
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'es',
            'title' => 'Zeta Unique Petition',
            'slug' => 'zeta-unique-petition',
            'description' => '<p>Body</p>',
        ]);

        return $petition;
    }

    public function test_default_locale_urls_redirect_to_the_translations_own_locale(): void
    {
        $petition = $this->petitionWithSpanishOnly();

        $this->get("/es/petition/zeta-unique-petition/{$petition->id}")->assertOk();

        $this->get("/en/petition/zeta-unique-petition/{$petition->id}")
            ->assertRedirect("/es/petition/zeta-unique-petition/{$petition->id}");
    }

    public function test_default_locale_listing_and_search_exclude_the_foreign_petition(): void
    {
        $this->petitionWithSpanishOnly();

        $this->get('/en/petitions?q=Zeta')->assertOk()->assertDontSee('Unique Petition');

        $this->get('/es/petitions?q=Zeta')->assertOk()->assertSee('Unique Petition');
    }
}
