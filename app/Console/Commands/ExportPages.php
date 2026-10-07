<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;

class ExportPages extends Command
{
    protected $signature = 'translations:export-pages';

    protected $description = 'Export English editorial pages (title, content) for Crowdin, keyed by English slug';

    public function handle(): int
    {
        $payload = ['pages' => []];

        PageTranslation::where('locale', 'en')
            ->orderBy('slug')
            ->get(['slug', 'title', 'content', 'published'])
            ->each(function (PageTranslation $row) use (&$payload) {
                $payload['pages'][$row->slug] = [
                    'title' => $row->title,
                    'content' => (string) $row->content,
                ];
            });

        $path = base_path('crowdin-content/pages-source.json');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");

        $this->info('Exported '.count($payload['pages']).' editorial pages to crowdin-content/pages-source.json');

        return self::SUCCESS;
    }
}
