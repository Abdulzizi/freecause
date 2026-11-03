<?php

namespace App\Console\Commands;

use App\Models\CategoryTranslation;
use App\Models\PageContent;
use App\Services\PageContentService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportDbTranslations extends Command
{
    protected $signature = 'translations:import-db {locale}';

    protected $description = 'Import translated database content for a locale from crowdin-content/<locale>.json';

    public function handle(): int
    {
        $locale = (string) $this->argument('locale');
        $path = base_path("crowdin-content/{$locale}.json");

        if (! is_file($path)) {
            $this->error("Missing file: {$path}");

            return self::FAILURE;
        }

        $data = json_decode((string) file_get_contents($path), true) ?: [];
        $pages = 0;
        $cats = 0;

        foreach ($data['page_contents'] ?? [] as $page => $keys) {
            foreach ((array) $keys as $key => $value) {
                if (! is_string($value) || $value === '') {
                    continue;
                }

                PageContent::updateOrCreate(
                    ['page' => $page, 'locale' => $locale, 'key' => $key],
                    ['value' => $value]
                );
                $pages++;
            }

            app(PageContentService::class)->clear((string) $page, $locale);
        }

        foreach ($data['categories'] ?? [] as $token => $name) {
            if (! is_string($name) || $name === '' || ! preg_match('/^c(\d+)$/', (string) $token, $m)) {
                continue;
            }

            CategoryTranslation::updateOrCreate(
                ['category_id' => (int) $m[1], 'locale' => $locale],
                ['name' => $name, 'slug' => Str::slug($name)]
            );
            $cats++;
        }

        $this->info("Imported {$pages} page content rows and {$cats} category translations for {$locale}");

        return self::SUCCESS;
    }
}
