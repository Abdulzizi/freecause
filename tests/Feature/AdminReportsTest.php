<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Report;
use App\Models\Signature;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);
    }

    private function levelWithPermissions(string $name, array $permissions): UserLevel
    {
        $level = UserLevel::create([
            'name' => $name,
            'visible_name' => $name,
            'is_system' => false,
        ]);

        foreach ($permissions as $module => $actions) {
            foreach ($actions as $action) {
                Permission::create([
                    'level_id' => $level->id,
                    'module' => $module,
                    'action' => $action,
                ]);
            }
        }

        return $level;
    }

    public function test_admin_can_view_the_reports_index(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.reports'))
            ->assertOk();
    }

    public function test_level_without_the_reports_permission_is_forbidden(): void
    {
        $level = $this->levelWithPermissions('logs_only', ['logs' => ['view']]);

        $this->actingAs(User::factory()->create(['level_id' => $level->id, 'verified' => true]), 'admin')
            ->get(route('admin.reports'))
            ->assertForbidden();
    }

    public function test_level_with_the_reports_view_permission_can_open_the_index(): void
    {
        $level = $this->levelWithPermissions('reports_viewer', ['reports' => ['view']]);

        $this->actingAs(User::factory()->create(['level_id' => $level->id, 'verified' => true]), 'admin')
            ->get(route('admin.reports'))
            ->assertOk();
    }

    public function test_index_lists_a_seeded_report(): void
    {
        Report::create([
            'reportable_type' => Petition::class,
            'reportable_id' => 4242,
            'reason' => 'copyright',
            'status' => 'open',
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.reports'))
            ->assertOk()
            ->assertSee('copyright')
            ->assertViewHas('reportTotal', 1);
    }

    public function test_index_filters_by_status(): void
    {
        Report::create([
            'reportable_type' => Petition::class,
            'reportable_id' => 4242,
            'reason' => 'copyright',
            'status' => 'open',
        ]);

        Report::create([
            'reportable_type' => Petition::class,
            'reportable_id' => 4242,
            'reason' => 'abuse',
            'status' => 'resolved',
        ]);

        $html = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.reports', ['status' => 'open']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('copyright', $html);
        $this->assertStringNotContainsString('abuse', $html);
    }

    public function test_detail_drawer_shows_a_petition_report(): void
    {
        $petition = Petition::factory()->create();

        $title = 'Unique petition headline '.Str::random(8);
        PetitionTranslation::factory()->create([
            'petition_id' => $petition->id,
            'locale' => default_locale(),
            'title' => $title,
        ]);

        $reporter = User::factory()->create(['name' => 'Unique Reporter Name']);

        $report = Report::create([
            'reportable_type' => Petition::class,
            'reportable_id' => $petition->id,
            'reason' => 'abuse',
            'details' => 'unique-detail-body',
            'reporter_id' => $reporter->id,
            'ip' => '203.0.113.7',
            'status' => 'open',
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.reports', ['select' => $report->id]))
            ->assertOk()
            ->assertSee($title)
            ->assertSee('unique-detail-body')
            ->assertSee('Unique Reporter Name')
            ->assertSee('203.0.113.7');
    }

    public function test_detail_drawer_shows_a_comment_report(): void
    {
        $signature = Signature::factory()->create(['text' => 'unique-comment-body']);

        $report = Report::create([
            'reportable_type' => Signature::class,
            'reportable_id' => $signature->id,
            'reason' => 'misinformation',
            'status' => 'open',
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.reports', ['select' => $report->id]))
            ->assertOk()
            ->assertSee('unique-comment-body');
    }

    public function test_report_with_a_deleted_subject_still_renders(): void
    {
        $report = Report::create([
            'reportable_type' => Petition::class,
            'reportable_id' => 987654,
            'reason' => 'other',
            'status' => 'open',
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.reports', ['select' => $report->id]))
            ->assertOk()
            ->assertSee('deleted');
    }

    public function test_update_status_changes_status_and_redirects_with_flash(): void
    {
        $report = Report::create([
            'reportable_type' => Petition::class,
            'reportable_id' => 12345,
            'reason' => 'spam',
            'status' => 'open',
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.reports.status'), [
                'id' => $report->id,
                'status' => 'resolved',
                'select' => $report->id,
                'locale' => 'en',
            ])
            ->assertRedirect(route('admin.reports', ['select' => $report->id, 'locale' => 'en']))
            ->assertSessionHas('success', 'saved');

        $this->assertSame('resolved', $report->fresh()->status);
    }

    public function test_update_status_rejects_an_unknown_status(): void
    {
        $report = Report::create([
            'reportable_type' => Petition::class,
            'reportable_id' => 12346,
            'reason' => 'spam',
            'status' => 'open',
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.reports.status'), [
                'id' => $report->id,
                'status' => 'bogus',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame('open', $report->fresh()->status);
    }

    public function test_update_status_requires_the_edit_permission(): void
    {
        $level = $this->levelWithPermissions('reports_viewer_only', ['reports' => ['view']]);

        $report = Report::create([
            'reportable_type' => Petition::class,
            'reportable_id' => 12347,
            'reason' => 'spam',
            'status' => 'open',
        ]);

        $this->actingAs(User::factory()->create(['level_id' => $level->id, 'verified' => true]), 'admin')
            ->post(route('admin.reports.status'), [
                'id' => $report->id,
                'status' => 'resolved',
            ])
            ->assertForbidden();

        $this->assertSame('open', $report->fresh()->status);
    }
}
