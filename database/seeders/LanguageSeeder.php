<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        Language::truncate();

        $languages = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'it', 'name' => 'Italian'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'es', 'name' => 'Spanish'],
        ];

        foreach ($languages as $index => $lang) {
            Language::create([
                'code' => $lang['code'],
                'name' => $lang['name'],
                'is_active' => true,
                'is_default' => $index === 0,
            ]);
        }
    }
}
