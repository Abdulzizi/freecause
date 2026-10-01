<?php

namespace Database\Seeders;

use App\Models\UserLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UserLevelSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        UserLevel::truncate();
        Schema::enableForeignKeyConstraints();

        // is_system = trusted admin level that bypasses permission checks
        // (canAccessAdmin()/CheckPermission). ONLY `admin` may be system-level;
        // `user`/`banned` must NOT grant admin access.
        UserLevel::insert([
            // [
            //     'name' => 'superadmin',
            //     'visible_name' => 'Superadmin',
            //     'is_system' => true,
            // ],
            [
                'name' => 'admin',
                'visible_name' => 'Admin',
                'is_system' => true,
            ],
            [
                'name' => 'user',
                'visible_name' => 'User',
                'is_system' => false,
            ],
            [
                'name' => 'banned',
                'visible_name' => 'Banned',
                'is_system' => false,
            ],
        ]);
    }
}
