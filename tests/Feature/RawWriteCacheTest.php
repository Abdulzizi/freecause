<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\CacheVersion;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RawWriteCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    private function publishedPetition(string $slug): Petition
    {
        $petition = Petition::factory()->create([
            'status' => 'published',
            'is_active' => true,
            'signature_count' => 0,
        ]);

        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'description' => 'Description',
        ]);

        return $petition;
    }

    private function versions(): array
    {
        return [
            'home' => CacheVersion::get('home'),
            'list' => CacheVersion::get('list'),
            'sitemap' => CacheVersion::get('sitemap'),
        ];
    }

    private function assertVersionsBumped(array $before): void
    {
        $this->assertNotSame($before['home'], CacheVersion::get('home'));
        $this->assertNotSame($before['list'], CacheVersion::get('list'));
        $this->assertNotSame($before['sitemap'], CacheVersion::get('sitemap'));
    }

    public function test_guest_sign_bumps_home_list_and_sitemap_versions(): void
    {
        $petition = $this->publishedPetition('raw-sign-petition');

        $before = $this->versions();

        $this->post("/en/petition/raw-sign-petition/{$petition->id}/sign", [
            'name' => 'Test',
            'email' => 'raw-sign@example.com',
            'comment' => 'I support this petition',
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ])->assertRedirect();

        $this->assertVersionsBumped($before);
    }

    public function test_reconcile_bumps_home_list_and_sitemap_versions(): void
    {
        $petition = Petition::factory()->create(['signature_count' => 999]);

        $admin = User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);

        $before = $this->versions();

        $this->actingAs($admin, 'admin')
            ->postJson('/admin/petitions/reconcile', ['id' => $petition->id])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertVersionsBumped($before);
    }
}
