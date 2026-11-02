<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserLevel;
use App\Services\WordPressUserDeletionService;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuditFollowupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    private function sysAdmin(array $o = []): User
    {
        return User::factory()->create($o + [
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);
    }

    private function editor(string $module, string $action): User
    {
        $level = UserLevel::create(['name' => "ed_{$module}_{$action}", 'visible_name' => 'Editor', 'is_system' => false]);
        Permission::create(['level_id' => $level->id, 'module' => $module, 'action' => $action]);
        Permission::create(['level_id' => $level->id, 'module' => $module, 'action' => 'view']);

        return User::factory()->create(['level_id' => $level->id, 'verified' => true]);
    }

    public function test_non_system_editor_cannot_edit_system_admin(): void
    {
        $target = $this->sysAdmin(['email' => 'root@example.com', 'password' => Hash::make('keep-me-123')]);

        $this->actingAs($this->editor('users', 'edit'), 'admin')
            ->post('/admin/users/save', ['id' => $target->id, 'email' => 'evil@example.com', 'password' => 'hijacked-123'])
            ->assertSessionHasErrors('id');

        $target->refresh();
        $this->assertSame('root@example.com', $target->email);
        $this->assertTrue(Hash::check('keep-me-123', $target->password));
    }

    public function test_system_admin_can_edit_another_system_admin(): void
    {
        $target = $this->sysAdmin();

        $this->actingAs($this->sysAdmin(), 'admin')
            ->post('/admin/users/save', ['id' => $target->id, 'password' => 'changed-pass-123'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('changed-pass-123', $target->fresh()->password));
    }

    public function test_admin_session_is_dropped_after_password_change(): void
    {
        $admin = $this->sysAdmin(['email' => 'adm@example.com', 'password' => Hash::make('old-password-1')]);

        $this->post('/admin/login', ['login' => 'adm@example.com', 'password' => 'old-password-1'])
            ->assertRedirect(route('admin.dashboard'));
        $this->get('/admin/dashboard')->assertOk();

        $admin->forceFill(['password' => Hash::make('new-password-1')])->save();
        app('auth')->forgetGuards();

        $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
    }

    public function test_bulk_delete_keeps_users_whose_wordpress_cleanup_fails(): void
    {
        $level = UserLevel::where('name', 'user')->value('id');
        $bad = User::factory()->create(['level_id' => $level]);
        $good = User::factory()->create(['level_id' => $level]);

        $this->mock(WordPressUserDeletionService::class, function ($m) use ($bad) {
            $m->shouldReceive('delete')->twice()->andReturnUsing(function (User $u) use ($bad) {
                if ($u->id === $bad->id) {
                    throw new \RuntimeException('wp down');
                }
            });
        });

        $this->actingAs($this->sysAdmin(), 'admin')
            ->postJson('/admin/users/bulk-delete', ['ids' => [$bad->id, $good->id]])
            ->assertOk()
            ->assertJson(['deleted' => 1, 'skipped' => 1]);

        $this->assertDatabaseHas('users', ['id' => $bad->id]);
        $this->assertDatabaseMissing('users', ['id' => $good->id]);
    }

    public function test_translate_missing_slug_stays_within_255_chars(): void
    {
        $slug = str_repeat('a', 255);
        $make = function (string $locale) use ($slug) {
            $id = DB::table('petitions')->insertGetId([
                'user_id' => $this->sysAdmin()->id, 'goal_signatures' => 1000, 'status' => 'draft',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('petition_translations')->insert([
                'petition_id' => $id, 'locale' => $locale, 'title' => 'T', 'slug' => $slug,
                'description' => 'd', 'created_at' => now(), 'updated_at' => now(),
            ]);

            return $id;
        };
        $make('fr');
        $id = $make('en');

        $this->actingAs($this->sysAdmin(), 'admin')
            ->postJson('/admin/petitions/translate-missing', ['id' => $id, 'locale' => 'en'])
            ->assertOk();

        $this->assertSame(0, DB::table('petition_translations')->whereRaw('length(slug) > 255')->count());
    }
}
