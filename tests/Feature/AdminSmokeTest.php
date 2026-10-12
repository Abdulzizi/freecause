<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        $this->admin = User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);
    }

    public function test_admin_login_page_renders(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('name="login"', false)
            ->assertSee('name="password"', false);
    }

    public function test_admin_login_with_email_redirects_to_dashboard(): void
    {
        $response = $this->post('/admin/login', [
            'login' => $this->admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin, 'admin');
    }

    public function test_admin_login_with_username_redirects_to_dashboard(): void
    {
        $response = $this->post('/admin/login', [
            'login' => $this->admin->name,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin, 'admin');
    }

    public function test_admin_login_regenerates_session_id_and_csrf_token(): void
    {
        $this->withSession([]);

        $oldId = session()->getId();

        $this->post('/admin/login', [
            'login' => $this->admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertNotSame($oldId, session()->getId());
        $this->assertAuthenticatedAs($this->admin, 'admin');
    }

    public function test_admin_login_rejects_wrong_password(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'login' => $this->admin->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest('admin');
    }

    public function test_admin_login_rejects_user_without_level(): void
    {
        $noLevel = User::factory()->create(['level_id' => null]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'login' => $noLevel->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest('admin');
    }

    public static function nonAdminLevels(): array
    {
        return [
            'user level' => ['user'],
            'banned level' => ['banned'],
        ];
    }

    public function test_only_admin_level_is_seeded_as_system(): void
    {
        $this->assertTrue((bool) UserLevel::where('name', 'admin')->value('is_system'));
        $this->assertFalse((bool) UserLevel::where('name', 'user')->value('is_system'));
        $this->assertFalse((bool) UserLevel::where('name', 'banned')->value('is_system'));
    }

    #[DataProvider('nonAdminLevels')]
    public function test_admin_login_rejects_non_admin_level(string $level): void
    {
        $member = User::factory()->create([
            'level_id' => UserLevel::where('name', $level)->value('id'),
            'verified' => true,
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'login' => $member->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest('admin');
    }

    #[DataProvider('nonAdminLevels')]
    public function test_non_admin_level_gets_403_on_admin_dashboard(string $level): void
    {
        $member = User::factory()->create([
            'level_id' => UserLevel::where('name', $level)->value('id'),
            'verified' => true,
        ]);

        $this->actingAs($member, 'admin')
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_unauthenticated_admin_request_redirects_to_login(): void
    {
        $this->get('/admin/dashboard')
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_logout_redirects_to_login(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/logout')
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
    }

    public static function adminIndexPages(): array
    {
        return [
            'dashboard' => ['/admin/dashboard'],
            'users' => ['/admin/users'],
            'petitions' => ['/admin/petitions'],
            'categories' => ['/admin/categories'],
            'signatures' => ['/admin/signatures'],
            'pages' => ['/admin/pages'],
            'spam' => ['/admin/spam'],
            'stats' => ['/admin/stats'],
            'logs' => ['/admin/logs'],
            'languages' => ['/admin/languages'],
            'global options' => ['/admin/options/global'],
            'language options' => ['/admin/options/language'],
            'ads' => ['/admin/ads'],
            'system user info' => ['/admin/system/user-info'],
            'system user levels' => ['/admin/system/user-levels'],
            'system permissions' => ['/admin/system/permissions'],
        ];
    }

    #[DataProvider('adminIndexPages')]
    public function test_admin_index_page_renders(string $uri): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get($uri)
            ->assertOk();
    }

    public function test_admin_dashboard_uses_blatui_markup(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('data-slot="page-header"', false)
            ->assertSee('data-slot="card"', false)
            ->assertSee('data-slot="badge"', false);
    }

    public function test_reconcile_endpoint_fixes_drifted_signature_count(): void
    {
        $petition = Petition::factory()->create(['signature_count' => 999]);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/petitions/reconcile', ['id' => $petition->id])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'petition_id' => $petition->id,
                'actual_count' => 0,
            ]);

        $this->assertSame(0, (int) $petition->fresh()->signature_count);
    }

    public function test_bulk_action_publishes_draft_petition(): void
    {
        $petition = Petition::factory()->create(['status' => 'draft']);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/petitions/bulk-action', [
                'action' => 'publish',
                'ids' => [$petition->id],
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame('published', $petition->fresh()->status);
    }

    public function test_logs_bulk_config_exposes_delete_route(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/logs')
            ->assertOk()
            ->assertDontSee('deleteRoute\u0022:null', false);
    }
}
