<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        Language::truncate();

        Language::insert([
            [
                'code' => 'en',
                'name' => 'English',
                'flag' => 'en_US.png',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'code' => 'fr',
                'name' => 'French',
                'flag' => 'fr_FR.png',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'code' => 'it',
                'name' => 'Italian',
                'flag' => 'it_IT.png',
                'is_active' => true,
                'is_default' => false,
            ],
        ]);
    }
}
