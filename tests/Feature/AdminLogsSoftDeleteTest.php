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
}
