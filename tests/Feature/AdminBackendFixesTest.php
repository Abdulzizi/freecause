<?php

namespace Tests\Feature;

use App\Models\Log;
use App\Models\Page;
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

/**
 * Regression coverage for the confirmed admin-panel defects: user-level
 * privilege escalation, reconcile signature counts, pages bulk delete,
 * signatures bulk delete permission, featured-flag divergence, homepage
 * key sanitization and the log export cap.
 */
class AdminBackendFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
    }

    protected function systemAdmin(): User
    {
        return User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);
    }

    protected function editorAdmin(string $module, string $action = 'edit'): User
    {
        $level = UserLevel::create([
            'name' => 'editor_'.$module.'_'.$action,
            'visible_name' => 'Editor',
            'is_system' => false,
        ]);

        Permission::create(['level_id' => $level->id, 'module' => $module, 'action' => $action]);
        Permission::create(['level_id' => $level->id, 'module' => $module, 'action' => 'view']);

        return User::factory()->create(['level_id' => $level->id, 'verified' => true]);
    }

    // ── 1a. Users: level changes restricted to system admins ────────────

    public function test_non_system_admin_cannot_change_user_level(): void
    {
        $editor = $this->editorAdmin('users');
        $target = User::factory()->create([
            'level_id' => UserLevel::where('name', 'user')->value('id'),
            'verified' => true,
        ]);

        $this->actingAs($editor, 'admin')
            ->post('/admin/users/save', ['id' => $target->id, 'level' => 'admin'])
            ->assertSessionHasErrors('level');

        $this->assertNotSame(UserLevel::where('name', 'admin')->value('id'), $target->fresh()->level_id);
    }

    public function test_system_admin_cannot_change_level_of_system_level_user(): void
    {
        $admin = $this->systemAdmin();
        $otherAdmin = User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post('/admin/users/save', ['id' => $otherAdmin->id, 'level' => 'user'])
            ->assertSessionHasErrors('level');

        $this->assertSame(UserLevel::where('name', 'admin')->value('id'), $otherAdmin->fresh()->level_id);
    }

    public function test_system_admin_can_change_level_of_regular_user(): void
    {
        $admin = $this->systemAdmin();
        $moderator = UserLevel::create([
            'name' => 'moderator',
            'visible_name' => 'Moderator',
            'is_system' => false,
        ]);
        $target = User::factory()->create([
            'level_id' => UserLevel::where('name', 'user')->value('id'),
            'verified' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post('/admin/users/save', ['id' => $target->id, 'level' => 'moderator'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame($moderator->id, $target->fresh()->level_id);
    }

    public function test_system_admin_cannot_assign_banned_level_through_profile_save(): void
    {
        $admin = $this->systemAdmin();
        $target = User::factory()->create([
            'level_id' => UserLevel::where('name', 'user')->value('id'),
            'verified' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post('/admin/users/save', ['id' => $target->id, 'level' => 'banned'])
            ->assertSessionHasErrors('level');

        $this->assertSame(UserLevel::where('name', 'user')->value('id'), $target->fresh()->level_id);
    }

    // ── 2. Reconcile counts confirmed signatures only ───────────────────

    public function test_reconcile_counts_only_confirmed_signatures(): void
    {
        $petition = Petition::factory()->create(['signature_count' => 999]);

        DB::table('signatures')->insert([
            ['petition_id' => $petition->id, 'name' => 'A', 'email' => 'a@x.test', 'locale' => 'en', 'confirmed' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['petition_id' => $petition->id, 'name' => 'B', 'email' => 'b@x.test', 'locale' => 'en', 'confirmed' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($this->systemAdmin(), 'admin')
            ->postJson('/admin/petitions/reconcile', ['id' => $petition->id])
            ->assertOk()
            ->assertJson(['actual_count' => 1]);

        $this->assertSame(1, (int) $petition->fresh()->signature_count);
    }

    // ── 3. Pages bulk delete removes parent rows (cascade) ──────────────

    public function test_pages_bulk_delete_removes_pages_and_all_translations(): void
    {
        $this->actingAs($this->systemAdmin(), 'admin');

        $page = Page::create();
        $kept = Page::create();

        foreach (['en', 'fr'] as $locale) {
            DB::table('page_translations')->insert([
                ['page_id' => $page->id, 'locale' => $locale, 'title' => 'Del '.$locale, 'slug' => 'del-'.$locale, 'published' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['page_id' => $kept->id, 'locale' => $locale, 'title' => 'Keep '.$locale, 'slug' => 'keep-'.$locale, 'published' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $this->post('/admin/pages/bulk-action', ['action' => 'delete', 'ids' => [$page->id]]);

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
        $this->assertDatabaseMissing('page_translations', ['page_id' => $page->id]);
        $this->assertDatabaseHas('pages', ['id' => $kept->id]);
        $this->assertDatabaseHas('page_translations', ['page_id' => $kept->id, 'locale' => 'en']);
    }

    // ── 4. Signatures bulk-action delete requires delete permission ─────

    public function test_signatures_bulk_action_delete_requires_delete_permission(): void
    {
        $editor = $this->editorAdmin('signatures');
        $petition = Petition::factory()->create();
        $signatureId = DB::table('signatures')->insertGetId([
            'petition_id' => $petition->id, 'name' => 'S', 'email' => 's@x.test', 'locale' => 'en', 'confirmed' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($editor, 'admin')
            ->postJson('/admin/signatures/bulk-action', ['action' => 'delete', 'ids' => [$signatureId]])
            ->assertForbidden();

        $this->assertDatabaseHas('signatures', ['id' => $signatureId]);

        // approve stays editable under signatures:edit
        $this->actingAs($editor, 'admin')
            ->postJson('/admin/signatures/bulk-action', ['action' => 'approve', 'ids' => [$signatureId]])
            ->assertOk();

        $this->assertSame(1, (int) DB::table('signatures')->where('id', $signatureId)->value('confirmed'));

        // system admin may delete
        $this->actingAs($this->systemAdmin(), 'admin')
            ->postJson('/admin/signatures/bulk-action', ['action' => 'delete', 'ids' => [$signatureId]])
            ->assertOk();

        $this->assertDatabaseMissing('signatures', ['id' => $signatureId]);
    }

    // ── 5. Featured flags stay in sync on save ──────────────────────────

    public function test_unfeaturing_petition_clears_all_translation_flags(): void
    {
        $petition = Petition::factory()->create(['is_featured' => 1]);

        foreach (['en', 'fr'] as $locale) {
            DB::table('petition_translations')->insert([
                ['petition_id' => $petition->id, 'locale' => $locale, 'title' => 'T '.$locale, 'slug' => 't-'.$locale, 'description' => 'd', 'is_featured' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $this->actingAs($this->systemAdmin(), 'admin')
            ->post('/admin/petitions/save', [
                'id' => $petition->id,
                'locale' => 'en',
                'status' => 'published',
                'title' => 'T en',
                'slug' => 't-en',
            ]);

        $this->assertSame(0, (int) $petition->fresh()->is_featured);
        $this->assertSame(0, DB::table('petition_translations')->where('petition_id', $petition->id)->where('is_featured', 1)->count());
    }

    public function test_featuring_petition_sets_edited_locale_translation_flag(): void
    {
        $petition = Petition::factory()->create(['is_featured' => 0]);

        $this->actingAs($this->systemAdmin(), 'admin')
            ->post('/admin/petitions/save', [
                'id' => $petition->id,
                'locale' => 'en',
                'status' => 'published',
                'title' => 'T en',
                'slug' => 't-en',
                'is_featured' => '1',
            ]);

        $this->assertSame(1, (int) $petition->fresh()->is_featured);
        $this->assertSame(1, DB::table('petition_translations')->where('petition_id', $petition->id)->where('locale', 'en')->value('is_featured'));
    }

    // ── 6. Homepage keys are sanitized like layout keys ─────────────────

    public function test_language_options_sanitize_homepage_keys(): void
    {
        $this->actingAs($this->systemAdmin(), 'admin')
            ->post('/admin/options/language', [
                'locale' => 'en',
                'h1' => '<strong>Title<script>alert(1)</script></strong>',
            ]);

        $value = DB::table('page_contents')->where('page', 'home')->where('locale', 'en')->where('key', 'h1')->value('value');

        $this->assertSame('<strong>Title</strong>', $value);
    }

    // ── 7. Log export respects the 10k cap ──────────────────────────────

    public function test_log_export_streams_all_logs_within_cap(): void
    {
        Log::create(['level' => 'info', 'title' => 'First', 'content' => 'x']);
        Log::create(['level' => 'error', 'title' => 'Second', 'content' => 'y']);

        $content = $this->actingAs($this->systemAdmin(), 'admin')
            ->get('/admin/logs/export')
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('First', $content);
        $this->assertStringContainsString('Second', $content);
        $this->assertSame(1, substr_count($content, 'First'));
    }
}
