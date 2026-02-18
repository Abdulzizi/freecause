<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $languages = Language::pluck('code')->toArray();

        $categories = [
            'Animals',
            'Business and Companies',
            'City Life',
            'Culture and Society',
            'Education',
            'Environment',
            'Health and Wellness',
            'Human Rights',
            'International Affairs',
            'Law and Justice',
            'Media and Entertainment',
            'Politics',
            'Religion and Spirituality',
            'Science and Technology',
            'Sports',
            'Transportation',
            'Travel and Tourism',
            'Work and Employment',
            'Youth and Family',
            'Food and Agriculture',
            'Housing and Urban Development',
            'Energy and Resources',
            'Public Safety',
        ];

        foreach ($categories as $i => $name) {

            $category = Category::firstOrCreate(
                ['sort_order' => $i + 1],
                ['is_active' => true]
            );

            foreach ($languages as $locale) {
                $translated = fake($locale)->words(3, true);

                $category->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'name' => $translated,
                        'slug' => Str::slug($translated),
                    ]
                );
            }
        }
    }
}
