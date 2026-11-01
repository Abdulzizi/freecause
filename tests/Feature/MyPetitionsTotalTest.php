<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\User;
use App\Support\CacheVersion;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MyPetitionsTotalTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_tab_total_counts_translated_petitions_and_is_cached(): void
    {
        $this->seed([LanguageSeeder::class, UserLevelSeeder::class]);
        Cache::flush();
        $user = User::factory()->create(['verified' => true, 'country' => 'US', 'gender' => 'm']);

        foreach (Petition::factory()->count(3)->create(['user_id' => $user->id]) as $p) {
            PetitionTranslation::create(['petition_id' => $p->id, 'locale' => 'en', 'title' => 't'.$p->id, 'slug' => 's'.$p->id, 'description' => 'd']);
        }
        Petition::factory()->create(['user_id' => $user->id]);

        $res = $this->actingAs($user)->get('/en/my-petitions?tab=created');

        $res->assertOk();
        $this->assertSame(3, $res->viewData('created')->total());
        $this->assertSame(3, Cache::get("my-petitions:total:created:{$user->id}:en:".CacheVersion::get('list')));
    }
}
