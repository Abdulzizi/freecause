<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\Signature;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\CacheVersion;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression: approving/rejecting/deleting signatures changed
 * petitions.signature_count with raw writes and no cache invalidation, so
 * listings kept stale counts.
 */
class AdminSignatureCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    public function test_approving_a_signature_recounts_and_bumps_listings(): void
    {
        $admin = User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);

        $petition = Petition::factory()->create(['signature_count' => 0]);
        $signature = Signature::factory()->create([
            'petition_id' => $petition->id,
            'confirmed' => false,
        ]);

        $before = [
            'home' => CacheVersion::get('home'),
            'list' => CacheVersion::get('list'),
            'sitemap' => CacheVersion::get('sitemap'),
        ];

        $this->actingAs($admin, 'admin')
            ->postJson('/admin/signatures/bulk-action', ['action' => 'approve', 'ids' => [$signature->id]])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'signature_count' => 1]);

        $this->assertNotSame($before['home'], CacheVersion::get('home'));
        $this->assertNotSame($before['list'], CacheVersion::get('list'));
        $this->assertSame($before['sitemap'], CacheVersion::get('sitemap'));
    }

    public function test_rejecting_a_confirmed_signature_decrements_and_bumps_listings(): void
    {
        $admin = User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);

        $petition = Petition::factory()->create(['signature_count' => 1]);
        $signature = Signature::factory()->create([
            'petition_id' => $petition->id,
            'confirmed' => true,
        ]);

        $before = CacheVersion::get('list');

        $this->actingAs($admin, 'admin')
            ->postJson('/admin/signatures/bulk-action', ['action' => 'reject', 'ids' => [$signature->id]])
            ->assertOk();

        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'signature_count' => 0]);
        $this->assertNotSame($before, CacheVersion::get('list'));
    }
}
