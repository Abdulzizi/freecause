<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConsolidateTranslations extends Command
{
    protected $signature = 'translations:consolidate';
    protected $description = 'Consolidate all translation sources into clean lang/ files';

    protected array $locales = ['en', 'da', 'de', 'el', 'es', 'fr', 'it', 'nl', 'pl', 'pt', 'ro', 'ru', 'sv', 'tr'];

    protected array $fileMap = [
        'auth'      => 'auth.php',
        'common'    => 'common.php',
        'contacts'  => 'contacts.php',
        'form'      => 'form.php',
        'home'      => 'home.php',
        'nav'       => 'nav.php',
        'petition'  => 'petition.php',
        'profile'   => 'profile.php',
        'sign'      => 'sign.php',
    ];

    // Groups that should be merged into petition.php
    protected array $petitionGroups = ['petition', 'show', 'sig', 'myp', 'create', 'thanks'];

    protected array $groupKeys = [];

    public function handle(): int
    {
        $this->info('=== TRANSLATION CONSOLIDATION STARTED ===');
        $this->newLine();

        $backupFile = $this->findLatestBackup();
        if (! $backupFile) {
            $this->error('No audit backup found. Run "php artisan translations:audit" first.');
            return Command::FAILURE;
        }

        $this->info("Using backup: {$backupFile}");
        $data = json_decode(File::get($backupFile), true);

        $this->info('Loading data from backup...');
        $this->loadFromLanguageLines($data['language_lines'] ?? []);
        $this->loadFromTranslationsTable($data['translations'] ?? []);
        $this->loadFromExistingLangFiles($data['lang_files'] ?? []);
        $this->loadFromPageContent($data['page_contents'] ?? []);
        $this->mergePetitionGroups();

        $this->newLine();
        $this->info("Loaded keys per group:");
        foreach ($this->groupKeys as $group => $locales) {
            $localeCount = count($locales);
            $enCount = count($locales['en'] ?? []);
            $this->line("  {$group}: {$enCount} keys ({$localeCount} locales)");
        }

        $this->newLine();
        $this->info('Generating consolidated lang files...');

        $langDir = base_path('lang');
        $totalFiles = 0;
        $totalKeys = 0;

        foreach ($this->locales as $locale) {
            $localeDir = "{$langDir}/{$locale}";
            File::ensureDirectoryExists($localeDir);

            foreach ($this->fileMap as $group => $filename) {
                $keys = $this->getKeysForLocale($group, $locale);
                if (empty($keys)) {
                    $this->warn("  SKIP: {$locale}/{$filename} (no keys for group '{$group}')");
                    continue;
                }

                $content = $this->generatePhpFile($keys);
                $filePath = "{$localeDir}/{$filename}";
                $written = File::put($filePath, $content);
                if ($written === false) {
                    $this->error("  FAILED to write: {$filePath}");
                } else {
                    $totalFiles++;
                    $totalKeys += count($keys);
                }
            }
        }

        $this->newLine();
        $this->info("Generated {$totalFiles} files with {$totalKeys} total key-locale pairs");

        $this->newLine();
        $this->info('Removing old lang files that are now superseded...');
        $this->removeOldLangFiles($langDir);

        $this->newLine();
        $this->info('=== CONSOLIDATION COMPLETE ===');
        $this->info('Next steps:');
        $this->line('1. Review generated lang files');
        $this->line('2. Run "php artisan translations:drop-tables" to remove DB tables');
        $this->line('3. Remove spatie/laravel-translation-loader package');
        $this->line('4. Delete old translation controllers/services');

        return Command::SUCCESS;
    }

    protected function findLatestBackup(): ?string
    {
        $backupDir = storage_path('app/translation-backup');
        if (! is_dir($backupDir)) return null;

        $files = File::glob("{$backupDir}/audit-*.json");
        if (empty($files)) return null;

        usort($files, fn ($a, $b) => filemtime($b) - filemtime($a));
        return $files[0];
    }

    protected function loadFromLanguageLines(array $rows): void
    {
        if (empty($rows)) return;

        foreach ($rows as $row) {
            $group = $row['group'];
            $key = $row['key'];
            $text = json_decode($row['text'], true);

            if (! is_array($text)) continue;

            foreach ($this->locales as $locale) {
                if (isset($text[$locale]) && $text[$locale] !== '') {
                    $this->groupKeys[$group][$locale][$key] = $text[$locale];
                }
            }
        }
    }

    protected function loadFromTranslationsTable(array $rows): void
    {
        if (empty($rows)) return;

        $allGroups = array_merge(array_keys($this->fileMap), $this->petitionGroups);

        foreach ($rows as $row) {
            $locale = $row['locale'];
            $group = $row['group'];
            $key = $row['key'];
            $value = $row['value'];

            if (! in_array($locale, $this->locales)) continue;
            if (! in_array($group, $allGroups)) continue;
            if ($value === null || $value === '') continue;

            $this->groupKeys[$group][$locale][$key] = $value;
        }
    }

    protected function loadFromExistingLangFiles(array $files): void
    {
        $allGroups = array_merge(array_keys($this->fileMap), $this->petitionGroups);

        foreach ($files as $path => $content) {
            if (! is_array($content)) continue;

            $parts = explode('/', $path);
            if (count($parts) !== 2) continue;

            $locale = $parts[0];
            $filename = $parts[1];

            if (! in_array($locale, $this->locales)) continue;

            foreach ($content as $key => $value) {
                if (! is_string($value)) continue;

                $dotPos = strpos($key, '.');
                if ($dotPos === false) continue;

                $group = substr($key, 0, $dotPos);
                $subKey = substr($key, $dotPos + 1);

                if (! in_array($group, $allGroups)) continue;

                $this->groupKeys[$group][$locale][$subKey] = $value;
            }
        }
    }

    protected function loadFromPageContent(array $rows): void
    {
        if (empty($rows)) return;

        foreach ($rows as $row) {
            $page = $row['page'];
            $locale = $row['locale'];
            $key = $row['key'];
            $value = $row['value'];

            if (! in_array($locale, $this->locales)) continue;
            if ($value === null || $value === '') continue;

            if ($page === 'home' || $page === 'navbar') {
                $this->groupKeys['home'][$locale][$key] = $value;
            } elseif ($page === 'layout') {
                $this->groupKeys['nav'][$locale][$key] = $value;
            } elseif ($page === 'petition_show') {
                $this->groupKeys['petition'][$locale][$key] = $value;
            } elseif ($page === 'petition_sign' || $page === 'petition_sign_form') {
                $this->groupKeys['sign'][$locale][$key] = $value;
            } elseif ($page === 'petition_thanks') {
                $this->groupKeys['petition'][$locale][$key] = $value;
            }
        }
    }

    protected function mergePetitionGroups(): void
    {
        foreach ($this->petitionGroups as $group) {
            if ($group === 'petition') continue;
            if (! isset($this->groupKeys[$group])) continue;

            foreach ($this->groupKeys[$group] as $locale => $keys) {
                foreach ($keys as $key => $value) {
                    $this->groupKeys['petition'][$locale]["{$group}.{$key}"] = $value;
                }
            }

            unset($this->groupKeys[$group]);
        }
    }

    protected function getKeysForLocale(string $group, string $locale): array
    {
        $keys = $this->groupKeys[$group][$locale] ?? [];

        if (empty($keys) && $locale !== 'en') {
            $keys = $this->groupKeys[$group]['en'] ?? [];
        }

        ksort($keys);
        return $keys;
    }

    protected function generatePhpFile(array $keys): string
    {
        $lines = ["<?php\n", "return ["];

        foreach ($keys as $key => $value) {
            $escapedValue = $this->escapePhpValue($value);
            $lines[] = "    '{$key}' => '{$escapedValue}',";
        }

        $lines[] = "];\n";
        return implode("\n", $lines);
    }

    protected function escapePhpValue(string $value): string
    {
        return str_replace(
            ["\\", "'", "\n", "\r", "\t"],
            ["\\\\", "\\'", "\\n", "\\r", "\\t"],
            $value
        );
    }

    protected function removeOldLangFiles(string $langDir): void
    {
        $oldFiles = ['messages.php', 'petition.php', 'auth.php', 'form.php', 'home.php'];

        foreach ($this->locales as $locale) {
            $localeDir = "{$langDir}/{$locale}";
            if (! is_dir($localeDir)) continue;

            foreach (File::files($localeDir) as $file) {
                $filename = $file->getFilename();
                if (in_array($filename, $oldFiles)) {
                    $inNewMap = in_array($filename, $this->fileMap);
                    if (! $inNewMap) {
                        File::delete($file->getPathname());
                        $this->line("  Removed: {$locale}/{$filename}");
                    }
                }
            }
        }
    }
}
