<?php

namespace Tests\Feature;

use App\Models\Log;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLogsSoftDeleteTest extends TestCase
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

    public function test_bulk_delete_soft_deletes_and_restore_recovers(): void
    {
        $log = Log::create(['level' => 'info', 'context' => 'e2e', 'title' => 'soft', 'content' => 'c']);

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.logs.bulkDelete'), ['ids' => [$log->id]])
            ->assertOk()
            ->assertJson(['ok' => true, 'deleted' => 1]);

        $this->assertSoftDeleted($log);

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.logs.restore'), ['ids' => [$log->id]])
            ->assertOk()
            ->assertJson(['ok' => true, 'restored' => 1]);

        $this->assertDatabaseHas('logs', ['id' => $log->id, 'deleted_at' => null]);
    }

    public function test_trashed_rows_still_resolvable_from_list_via_select(): void
    {
        $log = Log::create(['level' => 'warn', 'context' => 'e2e', 'title' => 'trashed', 'content' => 'c']);
        $log->delete();

        $res = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.logs', ['select' => $log->id]));
        $res->assertOk();
    }

    public function test_restore_without_eligible_rows_is_safe(): void
    {
        $log = Log::create(['level' => 'info', 'context' => 'e2e', 'title' => 'live', 'content' => 'c']);

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.logs.restore'), ['ids' => [$log->id]])
            ->assertOk()
            ->assertJson(['ok' => true, 'restored' => 0]);
    }

    public function test_csv_export_includes_soft_deleted_rows_shown_in_the_log_list(): void
    {
        $log = Log::create(['level' => 'info', 'context' => 'e2e', 'title' => 'trashed export', 'content' => 'c']);
        $log->delete();

        $content = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.logs.export'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('trashed export', $content);
    }

    public function test_restore_all_is_post_only_and_redirects_with_a_result(): void
    {
        $log = Log::create(['level' => 'info', 'context' => 'e2e', 'title' => 'recent', 'content' => 'c']);
        $log->delete();

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.logs.restoreAll'))
            ->assertMethodNotAllowed();

        $this->actingAs($this->admin(), 'admin')
            ->from(route('admin.logs'))
            ->post(route('admin.logs.restoreAll'))
            ->assertRedirect(route('admin.logs'))
            ->assertSessionHas('success', 'Restored 1 recently deleted log entries.');

        $this->assertDatabaseHas('logs', ['id' => $log->id, 'deleted_at' => null]);
    }

    public function test_log_page_uses_a_csrf_protected_post_form_for_restore_all(): void
    {
        Log::create(['level' => 'info', 'context' => 'e2e', 'title' => 'visible', 'content' => 'c']);

        $response = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.logs'))
            ->assertOk();

        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $forms = $xpath->query('//form[@method="post" and @action="'.route('admin.logs.restoreAll').'"]');

        $this->assertSame(1, $forms->length);
        $this->assertSame(1, $xpath->query('.//input[@name="_token"]', $forms->item(0))->length);
    }

    public function test_csv_export_applies_the_same_filters_as_the_log_list(): void
    {
        Log::create(['level' => 'info', 'context' => 'e2e', 'title' => 'kept export', 'content' => 'c', 'ip' => '10.0.0.1']);
        Log::create(['level' => 'info', 'context' => 'e2e', 'title' => 'other ip', 'content' => 'c', 'ip' => '192.168.0.1']);
        Log::create(['level' => 'info', 'context' => 'e2e', 'title' => 'not a prefix', 'content' => 'kept export', 'ip' => '10.0.0.2']);

        $content = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.logs.export', ['q' => 'kept', 'ip' => '10.0.']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('kept export', $content);
        $this->assertStringNotContainsString('other ip', $content);
        $this->assertStringNotContainsString('not a prefix', $content);
    }

    public function test_prune_and_bulk_delete_keep_admin_audit_rows(): void
    {
        $audit = Log::create(['level' => 'info', 'context' => 'admin.audit', 'title' => 'a', 'content' => 'c']);
        $other = Log::create(['level' => 'info', 'context' => 'e2e', 'title' => 'o', 'content' => 'c']);
        Log::query()->update(['created_at' => now()->subDays(10)]);

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.logs.prune'), ['days' => 1])
            ->assertRedirect();

        $this->assertNotSoftDeleted($audit);
        $this->assertSoftDeleted($other);

        $other->restore();

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.logs.bulkDelete'), ['ids' => [$audit->id, $other->id]])
            ->assertOk()
            ->assertJson(['ok' => true, 'deleted' => 1]);

        $this->assertNotSoftDeleted($audit);
        $this->assertSoftDeleted($other);
    }
}
