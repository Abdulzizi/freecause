<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;

class ExportPages extends Command
{
    protected $signature = 'translations:export-pages';

    protected $description = 'Export English editorial pages for Crowdin, with content split into paragraph blocks for translation';

    public function handle(): int
    {
        $payload = ['pages' => []];

        PageTranslation::where('locale', 'en')
            ->orderBy('slug')
            ->get(['slug', 'title', 'content'])
            ->each(function (PageTranslation $row) use (&$payload) {
                $payload['pages'][$row->slug] = [
                    'title' => $row->title,
                    'content' => $this->blocks((string) $row->content),
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

    private function blocks(string $content): array
    {
        if (preg_match_all('/.*?(?:<\/p>|<\/li>|<\/h[1-6]>|<\/div>|<\/ul>|<\/ol>|<br\s*\/?>)/is', $content, $m) && $m[0] !== []) {
            $blocks = [];
            foreach ($m[0] as $block) {
                if (trim($block) !== '') {
                    $blocks[] = $block;
                }
            }
            $tail = substr($content, strlen(implode('', $m[0])));
            if (trim($tail) !== '') {
                $blocks[] = $tail;
            }

            return $blocks;
        }

        return trim($content) === '' ? [] : [$content];
    }
}
