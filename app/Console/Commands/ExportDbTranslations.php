<?php

namespace App\Console\Commands;

use App\Models\CategoryTranslation;
use App\Models\PageContent;
use Illuminate\Console\Command;

class ExportDbTranslations extends Command
{
    protected $signature = 'translations:export-db';

    protected $description = 'Export English database content (page_contents, categories) for Crowdin';

    public function handle(): int
    {
        $payload = ['page_contents' => [], 'categories' => []];

        PageContent::where('locale', 'en')
            ->orderBy('page')
            ->orderBy('key')
            ->get(['page', 'key', 'value'])
            ->each(function (PageContent $row) use (&$payload) {
                if ($row->value === null || $row->value === '') {
                    return;
                }

                $payload['page_contents'][$row->page][$row->key] = $row->value;
            });

        CategoryTranslation::where('locale', 'en')
            ->orderBy('category_id')
            ->get(['category_id', 'name'])
            ->each(function (CategoryTranslation $row) use (&$payload) {
                $payload['categories']['c'.$row->category_id] = $row->name;
            });

        $path = base_path('crowdin-content/source.json');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents(
            $path,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
        );

        $this->info(sprintf(
            'Exported %d categories and page contents for %d pages to crowdin-content/source.json',
            count($payload['categories']),
            count($payload['page_contents'])
        ));

        return self::SUCCESS;
    }
}
