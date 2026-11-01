<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DashboardController;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminDashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->seed(LanguageSeeder::class);
        $this->seed(UserLevelSeeder::class);
    }

    public function test_scopes_split_real_and_synthetic_accounts(): void
    {
        User::factory()->create(['email' => 'real1@gmail.com']);
        User::factory()->create(['email' => 'real2@gmail.com']);
        User::factory()->create(['email' => 'legacy@internetpetition.org']);

        $this->assertSame(2, User::real()->count());
        $this->assertSame(1, User::synthetic()->count());
    }

    public function test_dashboard_headlines_real_users_not_raw_rows(): void
    {
        User::factory()->create(['email' => 'real1@gmail.com', 'verified' => 1]);
        User::factory()->create(['email' => 'real2@gmail.com', 'verified' => 0]);
        User::factory()->create(['email' => 'legacy@internetpetition.org', 'verified' => 1]);

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
        ]);

        $real = User::real()->count();
        $verifiedReal = User::real()->where('verified', 1)->count();
        $pct = round($verifiedReal / max($real, 1) * 100);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Real Users')
            ->assertSee('of 4 accounts')
            ->assertSee('1 legacy nick-only')
            ->assertSee("{$verifiedReal} verified ({$pct}%)");
    }

    public function test_real_users_is_total_minus_synthetic(): void
    {
        User::factory()->create(['email' => 'a@gmail.com']);
        User::factory()->create(['email' => 'b@gmail.com']);
        User::factory()->create(['email' => 'legacy@internetpetition.org']);

        $stats = app(DashboardController::class)->getStats();

        $this->assertSame(1, $stats['users_synthetic']);
        $this->assertSame(2, $stats['users_real']);
    }
}
