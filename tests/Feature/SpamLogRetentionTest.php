<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SpamLogRetentionTest extends TestCase
{
    use RefreshDatabase;

    private function seedLogs(): void
    {
        DB::table('spam_logs')->insert([
            ['type' => 'petition.review', 'ip' => '1.1.1.1', 'payload' => null, 'created_at' => now()->subDays(10)],
            ['type' => 'signature', 'ip' => '2.2.2.2', 'payload' => null, 'created_at' => now()->subDays(10)],
            ['type' => 'signature', 'ip' => '3.3.3.3', 'payload' => null, 'created_at' => now()],
        ]);
    }

    public function test_daily_prune_keeps_petition_review_logs(): void
    {
        $this->seedLogs();

        $event = collect(app(Schedule::class)->events())->first(function ($event) {
            if (! $event instanceof CallbackEvent) {
                return false;
            }
            // Match the closure whose source references spam_logs.
            $fn = new \ReflectionFunction((new \ReflectionProperty($event, 'callback'))->getValue($event));
            $lines = array_slice(file($fn->getFileName()), $fn->getStartLine() - 1, $fn->getEndLine() - $fn->getStartLine() + 1);

            return str_contains(implode('', $lines), 'spam_logs');
        });

        $this->assertNotNull($event);
        $event->run($this->app);

        $this->assertEqualsCanonicalizing(['1.1.1.1', '3.3.3.3'], DB::table('spam_logs')->pluck('ip')->all());
    }

    public function test_clear_keeps_petition_review_logs(): void
    {
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
        $this->seedLogs();

        $level = UserLevel::create(['name' => 'spam-admin', 'visible_name' => 'spam-admin', 'is_system' => false]);
        Permission::create(['level_id' => $level->id, 'module' => 'spam', 'action' => 'delete']);
        $user = User::factory()->create(['level_id' => $level->id, 'verified' => true]);

        $this->actingAs($user, 'admin')->post(route('admin.spam.clear'))->assertRedirect();

        $this->assertSame(['petition.review'], DB::table('spam_logs')->pluck('type')->all());
    }
}
