<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminLevelId = DB::table('user_levels')
            ->where('name', 'admin')
            ->value('id');

        if (! $adminLevelId) {
            return;
        }

        $modules = Permission::matrix();

        DB::transaction(function () use ($adminLevelId, $modules) {
            $allowed = [];

            foreach ($modules as $module => $actions) {
                foreach ($actions as $action) {
                    $allowed[] = $module.'|'.$action;

                    DB::table('permissions')->updateOrInsert(
                        [
                            'level_id' => $adminLevelId,
                            'module' => $module,
                            'action' => $action,
                        ],
                        [
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }

            DB::table('permissions')
                ->where('level_id', $adminLevelId)
                ->get(['id', 'module', 'action'])
                ->each(function ($row) use ($allowed, $adminLevelId) {
                    if (! in_array($row->module.'|'.$row->action, $allowed, true)) {
                        DB::table('permissions')
                            ->where('level_id', $adminLevelId)
                            ->where('id', $row->id)
                            ->delete();
                    }
                });
        });
    }
}
