<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminLevelId = DB::table('user_levels')
            ->where('name', 'admin')
            ->value('id');

        if (!$adminLevelId) {
            return;
        }

        $modules = [
            'users' => ['view', 'create', 'edit', 'delete'],
            'petitions' => ['view', 'create', 'edit', 'delete', 'feature'],
            'categories' => ['view', 'create', 'edit', 'delete'],
            'pages' => ['view', 'edit'],
            'fanpages' => ['view'],
            'signatures' => ['view', 'delete'],
            'spam' => ['view', 'edit', 'delete'],
            'logs' => ['view', 'delete'],
            'stats' => ['view'],
            'languages' => ['view', 'edit', 'delete'],
            'options' => ['view', 'edit'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                DB::table('permissions')->insert([
                    'level_id' => $adminLevelId,
                    'module' => $module,
                    'action' => $action,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
