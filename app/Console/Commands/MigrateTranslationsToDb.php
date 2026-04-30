<?php

namespace App\Console\Commands;

use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MigrateTranslationsToDb extends Command
{
    protected $signature = 'translations:migrate';

    protected $description = 'Migrate translations from messages.php files to database';

    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    public function handle()
    {
        $this->info('Starting translation migration...');

        $locales = ['en', 'da', 'de', 'el', 'es', 'fr', 'it', 'nl', 'pl', 'pt', 'ro', 'ru', 'sv', 'tr'];
        $totalMigrated = 0;

        foreach ($locales as $locale) {
            $this->info("Migrating locale: {$locale}");
            
            // Migrate messages.php
            $messagesPath = resource_path("lang/{$locale}/messages.php");
            if (File::exists($messagesPath)) {
                $messages = File::getRequire($messagesPath);
                $count = $this->migrateMessages($locale, $messages);
                $totalMigrated += $count;
                $this->info("  - Migrated {$count} keys from messages.php");
            }

            // Migrate page_contents for home and layout pages
            $pageContents = \App\Models\PageContent::where('locale', $locale)->get();
            $pageCount = 0;
            foreach ($pageContents as $pc) {
                $group = $pc->page === 'home' ? 'home' : 'pages';
                Translation::updateOrCreate(
                    ['locale' => $locale, 'group' => $group, 'key' => $pc->key],
                    ['value' => $pc->value, 'is_active' => true]
                );
                $pageCount++;
            }
            $totalMigrated += $pageCount;
            $this->info("  - Migrated {$pageCount} keys from page_contents");
        }

        // Clear cache
        $this->translationService->clearCache();
        
        $this->info("Migration complete! Total keys migrated: {$totalMigrated}");
    }

    protected function migrateMessages(string $locale, array $messages): int
    {
        $count = 0;
        
        foreach ($messages as $key => $value) {
            // Determine group from key prefix
            $parts = explode('.', $key, 2);
            $group = count($parts) === 2 ? $parts[0] : 'common';
            $keyName = count($parts) === 2 ? $parts[1] : $key;
            
            Translation::updateOrCreate(
                ['locale' => $locale, 'group' => $group, 'key' => $keyName],
                ['value' => $value, 'is_active' => true]
            );
            
            $count++;
        }
        
        return $count;
    }
}
