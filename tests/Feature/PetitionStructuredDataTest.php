<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetitionStructuredDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    public function test_petition_show_page_has_no_json_ld_block(): void
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
        ]);
        PetitionTranslation::factory()->create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'slug' => 'clean-petition',
        ]);

        $this->get("/en/petition/clean-petition/{$petition->id}")
            ->assertOk()
            ->assertDontSee('application/ld+json', false);
    }
}
