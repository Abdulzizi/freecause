<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
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

        foreach ($names as $i => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true, 'sort_order' => $i + 1]
            );
        }
    }
}
