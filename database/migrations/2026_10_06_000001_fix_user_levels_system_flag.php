<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data repair: only `admin` may be is_system=1 (id 2 "user" and id 3
 * "banned" were seeded as system levels by the pre-d911b6c0 seeder).
 * While any level other than admin is is_system=1, the ban/delete
 * protection treats every regular account as a protected system account,
 * so banning and deleting silently does nothing. Idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_levels')
            ->where('name', '!=', 'admin')
            ->where('is_system', 1)
            ->update(['is_system' => 0]);
    }

    public function down(): void
    {
        // Restoring the pre-fix (wrong) state is deliberately a no-op.
    }
};
