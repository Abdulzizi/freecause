<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegacyImport extends Command
{
    protected $signature = 'legacy:import {--phase= : Import phase to run} {--from-id= : Start legacy id} {--to-id= : End legacy id} {--chunk=5000 : Rows per chunk} {--dry-run : Perform no writes}';

    protected $description = 'Import data from the legacy database.';

    private array $prefixLocales = [
        'dk_' => 'da_DK',
        'at_' => 'de_AT',
        'ch_' => 'de_CH',
        'de_' => 'de_DE',
        'gr_' => 'el_GR',
        'au_' => 'en_AU',
        'ca_' => 'en_CA',
        'eu_' => 'en_EU',
        'uk_' => 'en_GB',
        'ie_' => 'en_IE',
        'in_' => 'en_IN',
        'nz_' => 'en_NZ',
        'ph_' => 'en_PH',
        'ws_' => 'en_US',
        'ar_' => 'es_AR',
        'co_' => 'es_CO',
        'com_es_' => 'es_ES',
        'mx_' => 'es_MX',
        'pe_' => 'es_PE',
        'ca_fr_' => 'fr_CA',
        'ch_fr_' => 'fr_CH',
        'fr_' => 'fr_FR',
        'ch_it_' => 'it_CH',
        'it_' => 'it_IT',
        'nl_' => 'nl_NL',
        'pl_' => 'pl_PL',
        'br_' => 'pt_BR',
        'pt_' => 'pt_PT',
        'ro_' => 'ro_RO',
        'ru_' => 'ru_RU',
        'se_' => 'sv_SE',
        'tr_' => 'tr_TR',
    ];

    private array $sigOptionsModes = [
        '0' => 'nick',
        '1' => 'full',
        '2' => 'name',
    ];

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

        if ($phase === 'categories') {
            return $this->importCategories((bool) $this->option('dry-run'));
        }

        if ($phase === 'users') {
            return $this->importUsers((bool) $this->option('dry-run'));
        }

        $this->error('Unsupported or missing --phase. Supported: categories, users');

        return self::FAILURE;
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

    private function importUsers(bool $dryRun): int
    {
        $levelId = DB::table('user_levels')->where('name', 'user')->value('id');

        if (! $levelId) {
            $this->error('User level not found. Seed user levels first.');

            return self::FAILURE;
        }

        $chunk = max(1, (int) $this->option('chunk'));

        $adminLevelIds = DB::table('user_levels')
            ->where('is_system', true)
            ->whereNotIn('name', ['user', 'banned'])
            ->pluck('id')
            ->all();

        $minId = $this->option('from-id') !== null
            ? (int) $this->option('from-id')
            : (int) DB::connection('legacy')->table('users')->min('id');

        $maxId = $this->option('to-id') !== null
            ? (int) $this->option('to-id')
            : (int) DB::connection('legacy')->table('users')->max('id');

        $processed = 0;
        $created = 0;
        $updated = 0;
        $invalid = 0;
        $skipped = 0;
        $invalidIds = [];

        for ($lo = $minId; $lo <= $maxId; $lo += $chunk) {
            $hi = min($lo + $chunk - 1, $maxId);

            $rows = DB::connection('legacy')->table('users')
                ->where('id', '>=', $lo)
                ->where('id', '<=', $hi)
                ->orderBy('id')
                ->get();

            if ($rows->isEmpty()) {
                continue;
            }

            $ids = $rows->pluck('id')->all();

            $existingIds = DB::table('users')->whereIn('id', $ids)->pluck('id')->all();
            $existingSet = array_flip($existingIds);

            if (! empty($adminLevelIds)) {
                $adminConflict = DB::table('users')
                    ->whereIn('id', $ids)
                    ->whereIn('level_id', $adminLevelIds)
                    ->value('id');

                if ($adminConflict !== null) {
                    $this->error("Aborted: target user {$adminConflict} has a system-admin level.");

                    return self::FAILURE;
                }
            }

            $records = [];
            foreach ($rows as $row) {
                $processed++;

                $email = strtolower(trim((string) $row->email));

                if ($email === '') {
                    $invalid++;
                    if (count($invalidIds) < 10) {
                        $invalidIds[] = (int) $row->id;
                    }

                    continue;
                }

                $registered = $row->dt_registered;
                if ($registered === null || $registered === '0000-00-00 00:00:00') {
                    $registered = now();
                }

                if (isset($existingSet[$row->id])) {
                    $updated++;
                } else {
                    $created++;
                }

                $records[] = [
                    'id' => $row->id,
                    'name' => $row->name,
                    'first_name' => $row->name,
                    'last_name' => $row->surname,
                    'nickname' => $row->username,
                    'email' => $email,
                    'password' => $row->password,
                    'locale' => $this->prefixLocales[$row->prefix] ?? 'en_US',
                    'city' => $row->city,
                    'ip' => $row->ip,
                    'identify_mode' => $this->sigOptionsModes[(string) $row->sig_options] ?? 'full',
                    'verified' => ((string) $row->active === '1' && (string) $row->deleted === '0'),
                    'level_id' => $levelId,
                    'created_at' => $registered,
                    'updated_at' => now(),
                ];
            }

            if ($dryRun || empty($records)) {
                continue;
            }

            DB::transaction(function () use ($records) {
                foreach (array_chunk($records, 1000) as $batch) {
                    DB::table('users')->upsert(
                        $batch,
                        ['id'],
                        ['name', 'first_name', 'last_name', 'nickname', 'email', 'password', 'locale', 'city', 'ip', 'identify_mode', 'verified', 'level_id', 'updated_at']
                    );
                }
            });
        }

        $this->info(($dryRun ? '[dry-run] ' : '').'User import complete.');
        $this->line("range: {$minId}-{$maxId} chunk: {$chunk} processed: {$processed}");
        $this->line("created: {$created} updated: {$updated} skipped: {$skipped}");
        $this->line('invalid: '.$invalid.(empty($invalidIds) ? '' : ' (ids: '.implode(', ', $invalidIds).')'));

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
