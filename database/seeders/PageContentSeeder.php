<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PageContent;

class PageContentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['page' => 'home', 'locale' => 'en', 'key' => 'hero_h1', 'value' => 'Change the World'],
            ['page' => 'home', 'locale' => 'en', 'key' => 'hero_h2', 'value' => 'Welcome to <span class="red">FreeCause - Online Petition</span>, the ultimate spot to kick off your online petition—let’s make some waves!'],
        ];

        foreach ($rows as $r) {
            PageContent::updateOrCreate(
                ['page' => $r['page'], 'locale' => $r['locale'], 'key' => $r['key']],
                ['value' => $r['value']]
            );
        }
    }
}
