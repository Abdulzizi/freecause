<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DropUnusedTwoFactorFieldsMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const COLUMNS = ['two_factor_secret', 'two_factor_enabled', 'two_factor_confirmed_at'];

    public function test_columns_are_gone_after_migrate_and_restored_on_down(): void
    {
        $this->assertFalse(Schema::hasColumns('users', self::COLUMNS));

        $migration = require database_path('migrations/2026_11_04_000002_drop_unused_two_factor_fields_from_users.php');
        $migration->down();
        $this->assertTrue(Schema::hasColumns('users', self::COLUMNS));

        $migration->up();
        $this->assertFalse(Schema::hasColumn('users', 'two_factor_secret'));
    }
}
