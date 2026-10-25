<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegacyShortLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(LanguageSeeder::class);
        $this->seed(UserLevelSeeder::class);
    }

    public function test_known_short_link_301s_to_the_canonical_petition_url(): void
    {
        $petition = Petition::factory()->create();
        PetitionTranslation::factory()->create([
            'petition_id' => $petition->id,
            'locale' => 'it',
            'slug' => 'richiesta-adsl-martellago',
        ]);
        DB::table('legacy_shortlinks')->insert(['short_link' => '1W64TW', 'petition_id' => $petition->id]);

        $this->get('/1W64TW')
            ->assertStatus(301)
            ->assertRedirect("/it/petition/richiesta-adsl-martellago/{$petition->id}");
    }

    public function test_unknown_short_link_falls_back_to_the_default_locale(): void
    {
        $this->get('/ZZZZZ9')
            ->assertStatus(302)
            ->assertRedirect('/'.default_locale());
    }

    public function test_a_lowercase_six_letter_path_keeps_its_legacy_redirect(): void
    {
        $this->get('/search')
            ->assertStatus(301)
            ->assertRedirect('/'.default_locale().'/search');
    }
}
