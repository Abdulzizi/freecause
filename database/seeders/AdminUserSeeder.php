<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminUser::updateOrCreate(
            ['email' => 'admin@freecause.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('secret123'),
                'is_active' => true,
            ]
        );
    }
}
