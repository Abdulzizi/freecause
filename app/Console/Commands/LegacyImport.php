<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegacyImport extends Command
{
    protected $signature = 'legacy:import {--phase= : Import phase to run} {--dry-run : Perform no writes}';

    protected $description = 'Import data from the legacy database.';

    private array $localePrefixes = [
        'de' => ['de_', 'at_', 'ch_'],
        'fr' => ['fr_', 'ca_fr_', 'ch_fr_'],
        'it' => ['it_', 'ch_it_'],
        'es' => ['com_es_', 'ar_', 'co_', 'mx_', 'pe_', 'es_'],
        'pt' => ['pt_', 'br_'],
        'da' => ['dk_'],
        'el' => ['gr_'],
        'nl' => ['nl_'],
        'pl' => ['pl_'],
        'ro' => ['ro_'],
        'ru' => ['ru_'],
        'sv' => ['se_'],
        'tr' => ['tr_'],
        'en' => ['ws_', 'au_', 'ca_', 'eu_', 'uk_', 'ie_', 'in_', 'nz_', 'ph_'],
    ];

    public function handle(): int
    {
        $phase = $this->option('phase');

        if ($phase !== 'categories') {
            $this->error('Unsupported or missing --phase. Supported: categories');

            return self::FAILURE;
        }

        return $this->importCategories((bool) $this->option('dry-run'));
    }

    private function importCategories(bool $dryRun): int
    {
        $locales = array_keys(config('language_flags', []));

        $rows = DB::connection('legacy')
            ->table('petition_categories')
            ->select('old_id', 'prefix', 'name')
            ->get();

        if ($rows->isEmpty()) {
            $this->error('No rows found in legacy petition_categories.');

            return self::FAILURE;
        }

        $byOldId = [];
        foreach ($rows as $row) {
            $byOldId[(int) $row->old_id][$row->prefix] = $row->name;
        }

        ksort($byOldId);

        $importedIds = array_map('intval', array_keys($byOldId));

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $missing = [];

        $usedSlugs = [];
        foreach (DB::table('category_translations')->select('category_id', 'locale', 'slug')->get() as $existing) {
            if (in_array((int) $existing->category_id, $importedIds, true)) {
                continue;
            }

            $usedSlugs[$existing->locale][$existing->slug] = (int) $existing->category_id;
        }

        $run = function () use ($byOldId, $locales, $dryRun, &$created, &$updated, &$skipped, &$missing, &$usedSlugs) {
            foreach ($byOldId as $oldId => $prefixNames) {
                $category = DB::table('categories')->where('id', $oldId)->first();

                if ($category) {
                    $updated++;
                } else {
                    $created++;
                }

                if (! $dryRun) {
                    if ($category) {
                        DB::table('categories')->where('id', $oldId)->update([
                            'is_active' => true,
                            'sort_order' => $oldId,
                            'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('categories')->insert([
                            'id' => $oldId,
                            'is_active' => true,
                            'sort_order' => $oldId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                foreach ($locales as $locale) {
                    $name = $this->resolveName($prefixNames, $locale);

                    if ($name === null) {
                        $missing[] = "{$oldId}:{$locale}";
                        $skipped++;

                        continue;
                    }

                    $slug = $this->uniqueSlug($locale, $name, (int) $oldId, $usedSlugs);

                    if (! $dryRun) {
                        $translation = DB::table('category_translations')
                            ->where('category_id', $oldId)
                            ->where('locale', $locale)
                            ->first();

                        if ($translation) {
                            DB::table('category_translations')
                                ->where('category_id', $oldId)
                                ->where('locale', $locale)
                                ->update(['name' => $name, 'slug' => $slug, 'updated_at' => now()]);
                        } else {
                            DB::table('category_translations')->insert([
                                'category_id' => $oldId,
                                'locale' => $locale,
                                'name' => $name,
                                'slug' => $slug,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
        };

        if ($dryRun) {
            $run();
        } else {
            DB::transaction($run);
        }

        $this->info(($dryRun ? '[dry-run] ' : '').'Category import complete.');
        $this->line("created: {$created}");
        $this->line("updated: {$updated}");
        $this->line("skipped: {$skipped}");
        $this->line('missing translations: '.count($missing).(empty($missing) ? '' : ' ('.implode(', ', $missing).')'));

        return self::SUCCESS;
    }

    private function resolveName(array $prefixNames, string $locale): ?string
    {
        foreach ($this->localePrefixes[$locale] ?? [] as $prefix) {
            if (isset($prefixNames[$prefix]) && trim((string) $prefixNames[$prefix]) !== '') {
                return $prefixNames[$prefix];
            }
        }

        return null;
    }

    private function uniqueSlug(string $locale, string $name, int $categoryId, array &$usedSlugs): string
    {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'category';
        }

        $slug = $base;
        $i = 2;

        while (isset($usedSlugs[$locale][$slug]) && $usedSlugs[$locale][$slug] !== $categoryId) {
            $slug = $base.'-'.$i;
            $i++;
        }

        $usedSlugs[$locale][$slug] = $categoryId;

        return $slug;
    }
}
