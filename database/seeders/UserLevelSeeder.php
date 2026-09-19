<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserLevel;
use Illuminate\Support\Facades\Schema;

class UserLevelSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        \App\Models\UserLevel::truncate();
        Schema::enableForeignKeyConstraints();

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
                'is_system' => true,
            ],
            [
                'name' => 'banned',
                'visible_name' => 'Banned',
                'is_system' => true,
            ],
        ]);
    }
}
