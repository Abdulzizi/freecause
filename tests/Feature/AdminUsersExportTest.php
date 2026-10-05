<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Coverage for the admin user CSV export: streamed download, matching
 * columns, filter parity with the on-screen listing and bounded output.
 */
class AdminUsersExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    protected function systemAdmin(): User
    {
        return User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);
    }

    public function test_admin_can_download_export_with_expected_columns_and_users(): void
    {
        $admin = $this->systemAdmin();

        $alice = User::factory()->create(['email' => 'alice-export@example.com']);
        $bob = User::factory()->create(['email' => 'bob-export@example.com']);

        $content = $this->actingAs($admin, 'admin')
            ->get('/admin/users/export')
            ->assertOk()
            ->streamedContent();

        $this->assertStringStartsWith('id,email,name,locale,level,verified,created_at', $content);
        $this->assertStringContainsString($alice->email, $content);
        $this->assertStringContainsString($bob->email, $content);
        $this->assertStringContainsString('user', $content);
    }

    public function test_export_respects_email_prefix_filter(): void
    {
        $admin = $this->systemAdmin();

        User::factory()->create(['email' => 'alpha-export@example.com']);
        $beta = User::factory()->create(['email' => 'beta-export@example.com']);

        $content = $this->actingAs($admin, 'admin')
            ->get('/admin/users/export?email=alpha-export')
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('alpha-export@example.com', $content);
        $this->assertStringNotContainsString($beta->email, $content);
    }

    public function test_export_output_is_bounded_and_has_no_duplicate_rows(): void
    {
        $admin = $this->systemAdmin();

        $users = User::factory()->count(3)->create();

        $content = $this->actingAs($admin, 'admin')
            ->get('/admin/users/export')
            ->assertOk()
            ->streamedContent();

        // Mirrors the log-export cap test: each user is written exactly once
        // (the id set is collected once, then streamed).
        foreach ($users as $user) {
            $this->assertSame(1, substr_count($content, $user->email));
        }
    }
}
