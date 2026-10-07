<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;

class ImportPages extends Command
{
    protected $signature = 'translations:import-pages {locale}';

    protected $description = 'Insert translated editorial pages for a locale from crowdin-content/pages-<locale>.json (never overwrites existing rows)';

    public function handle(): int
    {
        $locale = (string) $this->argument('locale');
        $path = base_path("crowdin-content/pages-{$locale}.json");

        if (! is_file($path)) {
            $this->error("Missing file: {$path}");

            return self::FAILURE;
        }

        $data = json_decode((string) file_get_contents($path), true) ?: [];
        $created = 0;
        $skipped = 0;

        foreach ($data['pages'] ?? [] as $enSlug => $values) {
            $en = PageTranslation::where('locale', 'en')->where('slug', $enSlug)->first();

            if (! $en) {
                continue;
            }

            $exists = PageTranslation::where('page_id', $en->page_id)->where('locale', $locale)->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            $title = $values['title'] ?? $en->title;
            $content = $values['content'] ?? null;

            if (! is_string($title) || trim($title) === '') {
                $title = $en->title;
            }

            PageTranslation::create([
                'page_id' => $en->page_id,
                'locale' => $locale,
                'title' => $title,
                'slug' => $en->slug,
                'content' => $content,
                'published' => $en->published,
            ]);
            $created++;
        }

        $this->info("Imported {$created} new pages for {$locale} ({$skipped} existing preserved)");

        return self::SUCCESS;
    }
}
