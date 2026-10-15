<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackfillIdentifyModeNickMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    public function test_it_flips_only_full_users_who_have_a_nickname(): void
    {
        // should flip: full + nickname set
        $flips = User::factory()->create(['identify_mode' => 'full', 'nickname' => 'Zuzu']);

        // should NOT flip: full but no nickname
        $noNick = User::factory()->create(['identify_mode' => 'full', 'nickname' => null]);

        // should NOT flip: full with empty-string nickname
        $emptyNick = User::factory()->create(['identify_mode' => 'full', 'nickname' => '']);

        // should NOT touch: explicit 'name' choice despite having a nickname
        $nameChoice = User::factory()->create(['identify_mode' => 'name', 'nickname' => 'Keep']);

        // already 'nick' -- untouched
        $alreadyNick = User::factory()->create(['identify_mode' => 'nick', 'nickname' => 'Already']);

        $migration = require database_path('migrations/2026_10_15_000001_backfill_identify_mode_nick_for_nickname_users.php');
        $migration->up();

        $this->assertSame('nick', $flips->fresh()->identify_mode);
        $this->assertSame('full', $noNick->fresh()->identify_mode);
        $this->assertSame('full', $emptyNick->fresh()->identify_mode);
        $this->assertSame('name', $nameChoice->fresh()->identify_mode);
        $this->assertSame('nick', $alreadyNick->fresh()->identify_mode);
    }

    public function test_down_reverts_nickname_users_to_full(): void
    {
        $user = User::factory()->create(['identify_mode' => 'full', 'nickname' => 'Zuzu']);

        $migration = require database_path('migrations/2026_10_15_000001_backfill_identify_mode_nick_for_nickname_users.php');
        $migration->up();
        $this->assertSame('nick', $user->fresh()->identify_mode);

        $migration->down();
        $this->assertSame('full', $user->fresh()->identify_mode);
    }
}
