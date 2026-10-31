<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\CacheVersion;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminPetitionDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        config(['edge-cache.purge_enabled' => false]);
        $this->withoutVite();
    }

    private function admin(): User
    {
        return User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);
    }

    public function test_delete_removes_all_petition_children_but_keeps_other_petitions_and_users(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $owner = User::factory()->create();
        $petition = Petition::factory()->create(['user_id' => $owner->id]);
        $kept = Petition::factory()->create(['user_id' => $owner->id]);
        foreach (['en', 'de'] as $locale) {
            PetitionTranslation::create([
                'petition_id' => $petition->id, 'locale' => $locale,
                'title' => 'Delete me', 'slug' => 'delete-me', 'description' => 'Test content',
            ]);
        }
        Signature::factory()->create(['petition_id' => $petition->id]);
        $keptSignature = Signature::factory()->create(['petition_id' => $kept->id]);
        DB::table('petition_views')->insert(['petition_id' => $petition->id, 'viewed_on' => '2026-10-31', 'views' => 1]);
        DB::table('legacy_shortlinks')->insert(['petition_id' => $petition->id, 'short_link' => 'DEL123']);
        Cache::put('legacy_shortlink:DEL123', (object) ['petition_id' => $petition->id], 3600);
        $before = CacheVersion::get('sitemap');
        $this->get(route('admin.petitions'))->assertOk()->assertSee('id="bulk-delete"', false);

        $this->postJson(route('admin.petitions.bulkDelete'), ['ids' => [$petition->id], 'confirmed' => true])
            ->assertOk()->assertJson(['ok' => true, 'deleted' => 1, 'restorable' => false]);

        $this->assertDatabaseMissing('petitions', ['id' => $petition->id]);
        foreach (['petition_translations', 'signatures', 'petition_views', 'legacy_shortlinks'] as $table) {
            $this->assertDatabaseMissing($table, ['petition_id' => $petition->id]);
        }
        $this->assertDatabaseHas('petitions', ['id' => $kept->id]);
        $this->assertDatabaseHas('signatures', ['id' => $keptSignature->id]);
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
        $this->assertNull(Cache::get('legacy_shortlink:DEL123'));
        $this->assertNotSame($before, CacheVersion::get('sitemap'));
    }

    public function test_delete_requires_explicit_confirmation_and_valid_selection(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $petition = Petition::factory()->create();
        $this->postJson(route('admin.petitions.bulkDelete'), ['ids' => [$petition->id]])
            ->assertUnprocessable()->assertJsonValidationErrors('confirmed');
        $this->postJson(route('admin.petitions.bulkDelete'), ['ids' => [], 'confirmed' => true])
            ->assertUnprocessable()->assertJsonValidationErrors('ids');
        $this->postJson(route('admin.petitions.bulkDelete'), ['ids' => [$petition->id, 999999999], 'confirmed' => true])
            ->assertUnprocessable();
        $this->assertDatabaseHas('petitions', ['id' => $petition->id]);
    }

    public function test_edit_permission_does_not_grant_delete_and_delete_does_not_require_edit(): void
    {
        $level = UserLevel::create(['name' => 'petition_editor', 'visible_name' => 'Editor', 'is_system' => false]);
        foreach (['view', 'edit'] as $action) {
            Permission::create(['level_id' => $level->id, 'module' => 'petitions', 'action' => $action]);
        }
        $editor = User::factory()->create(['level_id' => $level->id, 'verified' => true]);
        $petition = Petition::factory()->create();
        $this->actingAs($editor, 'admin')->postJson(route('admin.petitions.bulkDelete'), ['ids' => [$petition->id], 'confirmed' => true])
            ->assertForbidden();
        $this->assertDatabaseHas('petitions', ['id' => $petition->id]);

        // A separate permission level avoids carrying cached editor permissions.
        $deleterLevel = UserLevel::create(['name' => 'petition_deleter', 'visible_name' => 'Deleter', 'is_system' => false]);
        foreach (['view', 'delete'] as $action) {
            Permission::create(['level_id' => $deleterLevel->id, 'module' => 'petitions', 'action' => $action]);
        }
        $deleter = User::factory()->create(['level_id' => $deleterLevel->id, 'verified' => true]);
        $this->actingAs($deleter, 'admin')->postJson(route('admin.petitions.bulkDelete'), ['ids' => [$petition->id], 'confirmed' => true])
            ->assertOk();
    }

    public function test_guest_cannot_delete_petitions(): void
    {
        $petition = Petition::factory()->create();
        $this->postJson(route('admin.petitions.bulkDelete'), ['ids' => [$petition->id], 'confirmed' => true])
            ->assertRedirect(route('admin.login'));
        $this->assertDatabaseHas('petitions', ['id' => $petition->id]);
    }
}
