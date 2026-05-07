<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class AuditTranslations extends Command
{
    protected $signature = 'translations:audit';
    protected $description = 'Audit all translation sources and export a safety-net backup';

    protected array $report = [];
    protected array $export = [];

    public function handle(): int
    {
        $this->info('=== TRANSLATION AUDIT STARTED ===');
        $this->newLine();

        $this->auditLanguageLines();
        $this->auditTranslationsTable();
        $this->auditLangFiles();
        $this->auditPageContent();
        $this->auditViewUsage();
        $this->generateRecommendations();
        $this->exportBackup();
        $this->printReport();

        $this->newLine();
        $this->info('=== TRANSLATION AUDIT COMPLETE ===');

        return Command::SUCCESS;
    }

    protected function auditLanguageLines(): void
    {
        $this->section('DATABASE: language_lines (Spatie Translation Loader)');

        if (! Schema::hasTable('language_lines')) {
            $this->report['language_lines']['status'] = 'TABLE DOES NOT EXIST';
            $this->line('  Table does not exist.');
            return;
        }

        $total = DB::table('language_lines')->count();
        $this->export['language_lines'] = DB::table('language_lines')->get()->toArray();
        $this->report['language_lines']['total_rows'] = $total;

        if ($total === 0) {
            $this->report['language_lines']['status'] = 'EMPTY';
            $this->line("  Total rows: 0");
            $this->warn("  → SAFE TO DROP");
            return;
        }

        $groups = DB::table('language_lines')->distinct()->pluck('group')->toArray();
        $sample = DB::table('language_lines')->limit(5)->get(['group', 'key', 'text'])->toArray();

        $this->report['language_lines']['status'] = 'HAS DATA';
        $this->report['language_lines']['groups'] = $groups;
        $this->report['language_lines']['sample'] = $sample;

        $this->line("  Total rows: {$total}");
        $this->line("  Groups: " . implode(', ', $groups));
        $this->line("  Sample:");
        foreach ($sample as $row) {
            $textPreview = mb_substr($row->text, 0, 60);
            $this->line("    - {$row->group}.{$row->key} → {$textPreview}");
        }
    }

    protected function auditTranslationsTable(): void
    {
        $this->section('DATABASE: translations (Custom Translation System)');

        if (! Schema::hasTable('translations')) {
            $this->report['translations']['status'] = 'TABLE DOES NOT EXIST';
            return;
        }

        $total = DB::table('translations')->count();
        $this->export['translations'] = DB::table('translations')->get()->toArray();
        $this->report['translations']['total_rows'] = $total;

        if ($total === 0) {
            $this->report['translations']['status'] = 'EMPTY';
            $this->line("  Total rows: 0");
            $this->warn("  → SAFE TO DROP");
            return;
        }

        $locales = DB::table('translations')
            ->select('locale', DB::raw('count(*) as cnt'))
            ->groupBy('locale')
            ->orderBy('locale')
            ->get();

        $groups = DB::table('translations')->distinct()->pluck('group')->toArray();
        $sample = DB::table('translations')->limit(5)->get(['locale', 'group', 'key', 'value'])->toArray();

        $localeSummary = [];
        foreach ($locales as $l) {
            $localeSummary[$l->locale] = $l->cnt;
        }

        $this->report['translations']['status'] = 'HAS DATA';
        $this->report['translations']['locales'] = $localeSummary;
        $this->report['translations']['groups'] = $groups;
        $this->report['translations']['sample'] = $sample;

        $this->line("  Total rows: {$total}");
        $this->line("  Locales: " . json_encode($localeSummary));
        $this->line("  Groups: " . implode(', ', $groups));
        $this->warn("  → DEAD CODE: written by admin UI but NEVER read by views");
    }

    protected function auditLangFiles(): void
    {
        $this->section('FILES: lang/ directory');

        $langDir = resource_path('lang');
        $this->export['lang_files'] = [];
        $fileReport = [];

        if (! is_dir($langDir)) {
            $this->line('  lang/ directory does not exist.');
            return;
        }

        $locales = array_filter(scandir($langDir), fn ($d) => ! str_starts_with($d, '.'));
        $totalKeysAll = 0;

        foreach ($locales as $locale) {
            $localePath = "{$langDir}/{$locale}";
            if (! is_dir($localePath)) continue;

            $files = array_filter(scandir($localePath), fn ($f) => str_ends_with($f, '.php'));
            $localeKeys = [];
            $localeFileReport = [];

            foreach ($files as $file) {
                $filePath = "{$localePath}/{$file}";
                $content = @include $filePath;
                if (! is_array($content)) continue;

                $keyCount = count($content, COUNT_RECURSIVE) - count($content);
                $localeKeys = array_merge($localeKeys, array_keys($content));
                $localeFileReport[$file] = $keyCount;
                $totalKeysAll += $keyCount;

                $this->export['lang_files']["{$locale}/{$file}"] = $content;
            }

            $fileCount = count($files);
            $fileReport[$locale] = [
                'files' => $localeFileReport,
                'total_keys' => count($localeKeys),
                'file_count' => $fileCount,
                'files_list' => $files,
            ];

            $filesStr = implode(', ', array_map(fn ($f, $c) => "{$f}({$c})", $localeFileReport, array_values($localeFileReport)));
            $this->line("  {$locale}: {$filesStr}");
        }

        $this->report['lang_files'] = $fileReport;
        $this->report['lang_files']['total_keys_all'] = $totalKeysAll;

        $fileCounts = array_map(fn ($r) => $r['file_count'], $fileReport);
        if (count(array_unique($fileCounts)) > 1) {
            $this->warn("  ⚠ INCONSISTENT: Locales have different file structures");
        }
    }

    protected function auditPageContent(): void
    {
        $this->section('DATABASE: page_contents (Admin-Managed CMS Content)');

        if (! Schema::hasTable('page_contents')) {
            $this->report['page_contents']['status'] = 'TABLE DOES NOT EXIST';
            return;
        }

        $total = DB::table('page_contents')->count();
        $this->export['page_contents'] = DB::table('page_contents')->get()->toArray();

        $pages = DB::table('page_contents')
            ->select('page', 'locale', DB::raw('count(*) as cnt'))
            ->groupBy('page', 'locale')
            ->orderBy('page')
            ->orderBy('locale')
            ->get();

        $pageSummary = [];
        foreach ($pages as $p) {
            if (! isset($pageSummary[$p->page])) {
                $pageSummary[$p->page] = [];
            }
            $pageSummary[$p->page][$p->locale] = $p->cnt;
        }

        $keys = DB::table('page_contents')->distinct()->pluck('key')->toArray();

        $this->report['page_contents'] = [
            'total_rows' => $total,
            'pages' => $pageSummary,
            'keys' => $keys,
            'status' => 'ACTIVE',
        ];

        $this->line("  Total rows: {$total}");
        $this->line("  Pages: " . json_encode($pageSummary));
        $this->line("  Keys: " . implode(', ', $keys));
        $this->info("  → ACTIVE (used by home view and footer)");
    }

    protected function auditViewUsage(): void
    {
        $this->section('VIEW USAGE: What views actually call');

        $viewsDir = resource_path('views');
        $allKeys = [];
        $allFiles = [];

        $bladeFiles = $this->findBladeFiles($viewsDir);

        foreach ($bladeFiles as $bladeFile) {
            $content = File::get($bladeFile);
            $relativePath = str_replace($viewsDir . '/', '', $bladeFile);

            preg_match_all("/__\(['\"]([a-z_]+\.[a-z_]+)/", $content, $matches);
            if (! empty($matches[1])) {
                foreach ($matches[1] as $key) {
                    $allKeys[$key] = ($allKeys[$key] ?? 0) + 1;
                    if (! isset($allFiles[$key])) {
                        $allFiles[$key] = [];
                    }
                    $allFiles[$key][] = $relativePath;
                }
            }
        }

        ksort($allKeys);

        $groups = [];
        foreach ($allKeys as $key => $count) {
            $group = explode('.', $key)[0];
            if (! isset($groups[$group])) {
                $groups[$group] = [];
            }
            $groups[$group][$key] = $count;
        }

        $this->report['view_usage'] = [
            'total_unique_keys' => count($allKeys),
            'groups' => array_keys($groups),
            'keys' => $allKeys,
            'key_files' => $allFiles,
        ];

        $this->line("  Total unique keys: " . count($allKeys));
        $this->line("  Groups used: " . implode(', ', array_keys($groups)));
        $this->line("  Keys by group:");
        foreach ($groups as $group => $keys) {
            $this->line("    {$group}: " . count($keys) . " keys");
        }

        $this->checkMissingKeys($allKeys);
    }

    protected function checkMissingKeys(array $viewKeys): void
    {
        $this->newLine();
        $this->line('  --- MISSING KEY ANALYSIS ---');

        $missingInFiles = [];
        foreach ($viewKeys as $key => $count) {
            $parts = explode('.', $key, 2);
            $group = $parts[0];
            $subKey = $parts[1];

            $found = false;
            foreach ($this->report['lang_files'] as $locale => $info) {
                if (! is_array($info) || isset($info['files'])) continue;
                foreach ($info['files'] as $file => $fileKeyCount) {
                    $fileName = str_replace('.php', '', $file);
                    if ($fileName === $group) {
                        $filePath = resource_path("lang/{$locale}/{$file}");
                        if (File::exists($filePath)) {
                            $content = @include $filePath;
                            if (is_array($content) && isset($content[$subKey])) {
                                $found = true;
                                break;
                            }
                        }
                    }
                }
                if ($found) break;
            }

            if (! $found) {
                $missingInFiles[$key] = $count;
            }
        }

        if (! empty($missingInFiles)) {
            $this->warn("  Keys used in views but NOT found in any lang file:");
            foreach ($missingInFiles as $key => $count) {
                $this->warn("    - {$key} (used {$count}x)");
            }
        } else {
            $this->info("  All view keys found in lang files.");
        }
    }

    protected function generateRecommendations(): void
    {
        $this->section('RECOMMENDATIONS');

        $recs = [];

        if (($this->report['language_lines']['total_rows'] ?? 0) === 0) {
            $recs[] = 'language_lines table: SAFE TO DROP (empty)';
        } elseif (($this->report['language_lines']['total_rows'] ?? 0) > 0) {
            $recs[] = 'language_lines table: HAS DATA - export before dropping';
        }

        if (($this->report['translations']['total_rows'] ?? 0) === 0) {
            $recs[] = 'translations table: SAFE TO DROP (empty)';
        } elseif (($this->report['translations']['total_rows'] ?? 0) > 0) {
            $recs[] = 'translations table: SAFE TO DROP (dead code, but export ' . ($this->report['translations']['total_rows'] ?? 0) . ' rows first)';
        }

        $recs[] = 'lang/ files: Restructure into 9 files per locale';
        $recs[] = 'Home view: Admin-managed keys should use $content[] instead of __()';

        foreach ($recs as $i => $rec) {
            $this->line("  " . ($i + 1) . ". {$rec}");
        }

        $this->report['recommendations'] = $recs;
    }

    protected function exportBackup(): void
    {
        $backupDir = storage_path('app/translation-backup');
        File::ensureDirectoryExists($backupDir);

        $filename = 'audit-' . date('Y-m-d-His') . '.json';
        $filepath = "{$backupDir}/{$filename}";

        File::put($filepath, json_encode($this->export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->info("=== EXPORT ===");
        $this->line("Full data exported to: {$filepath}");
        $this->line("File size: " . round(File::size($filepath) / 1024, 1) . ' KB');
    }

    protected function printReport(): void
    {
        $this->newLine();
        $this->info('=== FULL REPORT (JSON) ===');
        $this->line(json_encode($this->report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    protected function section(string $title): void
    {
        $this->newLine();
        $this->info("--- {$title} ---");
    }

    protected function findBladeFiles(string $dir): array
    {
        $files = [];
        $items = File::allFiles($dir);
        foreach ($items as $item) {
            if (str_ends_with($item->getFilename(), '.blade.php')) {
                $files[] = $item->getPathname();
            }
        }
        return $files;
    }
}
