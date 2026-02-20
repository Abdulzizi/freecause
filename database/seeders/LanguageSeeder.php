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

            ['code' => 'da', 'name' => 'Danish'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'el', 'name' => 'Greek'],
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'es', 'name' => 'Spanish'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'it', 'name' => 'Italian'],
            ['code' => 'nl', 'name' => 'Dutch'],
            ['code' => 'pl', 'name' => 'Polish'],
            ['code' => 'pt', 'name' => 'Portuguese'],
            ['code' => 'ro', 'name' => 'Romanian'],
            ['code' => 'ru', 'name' => 'Russian'],
            ['code' => 'sv', 'name' => 'Swedish'],
            ['code' => 'tr', 'name' => 'Turkish'],

        ];

        foreach ($languages as $index => $lang) {
            Language::create([
                'code' => $lang['code'],
                'name' => $lang['name'],
                'is_active' => true,
                'is_default' => $lang['code'] === 'en',
            ]);
        }
    }
}
