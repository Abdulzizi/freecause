<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Petition;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminPetitionPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
    }

    protected ?User $petitionsAdmin = null;

    protected function petitionsEditor(): User
    {
        if ($this->petitionsAdmin && $this->petitionsAdmin->exists) {
            return $this->petitionsAdmin;
        }

        $level = UserLevel::create([
            'name' => 'editor_petitions',
            'visible_name' => 'Editor',
            'is_system' => false,
        ]);

        Permission::create(['level_id' => $level->id, 'module' => 'petitions', 'action' => 'edit']);
        Permission::create(['level_id' => $level->id, 'module' => 'petitions', 'action' => 'view']);

        return $this->petitionsAdmin = User::factory()->create([
            'level_id' => $level->id,
            'verified' => true,
        ]);
    }

    public function test_create_petition_builds_draft_with_translation_and_dedupes_slug(): void
    {
        $admin = $this->petitionsEditor();

        $response = $this->actingAs($admin, 'admin')->post('/admin/petitions/create', [
            'title' => 'Test Petition',
            'locale' => 'en',
            'goal_signatures' => 1000,
        ]);

        $id = Petition::query()->max('id');
        $response->assertRedirect(route('admin.petitions', ['select' => $id, 'locale' => 'en']));
        $response->assertSessionHas('success', 'petition #'.$id.' created');

        $petition = DB::table('petitions')->where('id', $id)->first();
        $this->assertSame('draft', $petition->status);
        $this->assertSame(1000, (int) $petition->goal_signatures);
        $this->assertSame($admin->id, (int) $petition->user_id);

        $translation = DB::table('petition_translations')
            ->where('petition_id', $id)
            ->where('locale', 'en')
            ->first();

        $this->assertNotNull($translation);
        $this->assertSame('Test Petition', $translation->title);
        $this->assertSame('test-petition', $translation->slug);

        $this->actingAs($admin, 'admin')
            ->post('/admin/petitions/create', ['title' => 'Test Petition', 'locale' => 'en'])
            ->assertRedirect();

        $slugs = DB::table('petition_translations')->where('locale', 'en')->pluck('slug');
        $this->assertTrue($slugs->contains('test-petition-1'));
        $this->assertSame(2, $slugs->count());
        $this->assertSame(2, $slugs->unique()->count());
    }

    public function test_translate_missing_seeds_drafts_from_source_locale(): void
    {
        $admin = $this->petitionsEditor();

        $petitionId = $this->createPetitionViaDb('Source Petition', 'en', 'source-petition');

        $expectedLanguages = DB::table('languages')->where('is_active', 1)->count() - 1;

        $this->actingAs($admin, 'admin')
            ->postJson('/admin/petitions/translate-missing', ['id' => $petitionId, 'locale' => 'en'])
            ->assertOk()
            ->assertJson(['ok' => true, 'created' => $expectedLanguages, 'missing' => 0]);

        $rows = DB::table('petition_translations')->where('petition_id', $petitionId)->get();
        $this->assertCount($expectedLanguages + 1, $rows);
        $this->assertTrue($rows->every(fn ($row) => $row->slug === 'source-petition'));
        $this->assertTrue($rows->contains(fn ($row) => $row->locale === 'fr' && $row->title === 'Source Petition'));

        $this->actingAs($admin, 'admin')
            ->postJson('/admin/petitions/translate-missing', ['id' => $petitionId])
            ->assertOk()
            ->assertJson(['ok' => true, 'created' => 0, 'missing' => 0]);
    }

    public function test_translate_missing_without_source_translation_fails_cleanly(): void
    {
        $admin = $this->petitionsEditor();

        $petitionId = DB::table('petitions')->insertGetId([
            'user_id' => $admin->id,
            'goal_signatures' => 1000,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($admin, 'admin')
            ->postJson('/admin/petitions/translate-missing', ['id' => $petitionId])
            ->assertNotFound()
            ->assertJson(['ok' => false, 'error' => 'no source translation']);

        $this->assertSame(
            0,
            DB::table('petition_translations')->where('petition_id', $petitionId)->count()
        );
    }

    public function test_save_still_updates_the_petition_after_cache_touches(): void
    {
        $admin = $this->petitionsEditor();

        $petitionId = $this->createPetitionViaDb('Old Title', 'en', 'old-title');

        $this->actingAs($admin, 'admin')
            ->post('/admin/petitions/save', [
                'id' => $petitionId,
                'locale' => 'en',
                'status' => 'published',
                'title' => 'New Title',
                'slug' => 'new-title',
                'goal_signatures' => 5000,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.petitions', ['select' => $petitionId, 'locale' => 'en']))
            ->assertSessionHas('success', 'saved');

        $this->assertSame('published', DB::table('petitions')->where('id', $petitionId)->value('status'));
        $this->assertSame('New Title', DB::table('petition_translations')
            ->where('petition_id', $petitionId)->where('locale', 'en')->value('title'));
    }

    private function createPetitionViaDb(string $title, string $locale, string $slug): int
    {
        $id = DB::table('petitions')->insertGetId([
            'user_id' => $this->petitionsEditor()->id,
            'goal_signatures' => 1000,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('petition_translations')->insert([
            'petition_id' => $id,
            'locale' => $locale,
            'title' => $title,
            'slug' => $slug,
            'description' => 'source body',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) $id;
    }
}
