<?php

namespace Tests\Feature;

use App\Console\Commands\WarmCache;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use App\Support\CacheKeys;
use App\Support\CacheVersion;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecentActivityPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_warmed_recent_activity_respects_current_identity_and_guest_names(): void
    {
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        $petition = Petition::factory()->create(['status' => 'published', 'is_active' => true]);
        PetitionTranslation::factory()->create(['petition_id' => $petition->id, 'locale' => 'en']);
        foreach (['nick' => 'publicnick', 'name' => 'Given', 'full' => 'Private Fullname'] as $mode => $expected) {
            $user = User::factory()->create(['name' => 'Private Fullname', 'first_name' => 'Given', 'nickname' => 'publicnick', 'identify_mode' => $mode]);
            Signature::factory()->create(['petition_id' => $petition->id, 'user_id' => $user->id, 'name' => 'Old snapshot', 'confirmed' => true, 'is_spam' => false]);
            cache()->forget(CacheKeys::homeRecent('en'));
            $method = new \ReflectionMethod(WarmCache::class, 'warmHomeRecent');
            $method->invoke(app(WarmCache::class), 'en', 'en');
            $row = collect(cache()->get(CacheKeys::homeRecent('en')))->firstWhere('user_id', $user->id);
            $this->assertSame($expected, $row->display_name);
        }
        Signature::factory()->create(['petition_id' => $petition->id, 'user_id' => null, 'name' => 'Guest public name', 'confirmed' => true, 'is_spam' => false]);
        cache()->forget(CacheKeys::homeRecent('en'));
        $method->invoke(app(WarmCache::class), 'en', 'en');
        $guest = collect(cache()->get(CacheKeys::homeRecent('en')))->firstWhere('user_id', null);
        $this->assertSame('Guest public name', $guest->display_name);
        $this->get('/en')->assertOk()->assertSee('publicnick');
    }

    public function test_identity_changes_invalidate_public_home_cache(): void
    {
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        $user = User::factory()->create(['identify_mode' => 'full', 'nickname' => 'publicnick']);
        $before = CacheVersion::get('home');
        $user->update(['identify_mode' => 'nick']);
        $this->assertNotSame($before, CacheVersion::get('home'));
        $before = CacheVersion::get('home');
        $user->update(['city' => 'Rome']);
        $this->assertSame($before, CacheVersion::get('home'));
    }
}
