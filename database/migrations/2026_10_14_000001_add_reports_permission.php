<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $actions = Permission::matrix()['reports'];
        $now = now();

        $levelIds = DB::table('user_levels')
            ->where('is_system', true)
            ->pluck('id');

        foreach ($levelIds as $levelId) {
            foreach ($actions as $action) {
                DB::table('permissions')->updateOrInsert(
                    [
                        'level_id' => $levelId,
                        'module' => 'reports',
                        'action' => $action,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('permissions')->where('module', 'reports')->delete();
    }
};
