<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeleteKeepsPetitionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_owner_keeps_petition_and_signatures(): void
    {
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        $this->withoutVite();
        $owner = User::factory()->create();
        $petition = Petition::factory()->create(['user_id' => $owner->id, 'status' => 'published', 'is_active' => true]);
        PetitionTranslation::create([
            'petition_id' => $petition->id, 'locale' => 'en',
            'title' => 'Orphan petition', 'slug' => 'orphan-petition', 'description' => 'Description',
        ]);
        $signature = Signature::factory()->create(['petition_id' => $petition->id]);

        $owner->delete();

        $this->assertNull($petition->fresh()->user_id);
        $this->assertNotNull($signature->fresh());
        $this->get(route('petition.show', ['locale' => 'en', 'slug' => 'orphan-petition', 'id' => $petition->id]))
            ->assertOk()
            ->assertSee('Orphan petition');
    }
}
