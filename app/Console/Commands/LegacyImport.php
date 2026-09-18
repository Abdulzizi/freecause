<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        if ($phase === 'petitions') {
            return $this->importPetitions((bool) $this->option('dry-run'));
        }

        if ($phase === 'petition-assets') {
            return $this->importPetitionAssets((bool) $this->option('dry-run'));
        }

        if ($phase === 'signatures') {
            return $this->importSignatures((bool) $this->option('dry-run'));
        }

        if ($phase === 'signature-counts') {
            return $this->rebuildSignatureCounts((bool) $this->option('dry-run'));
        }

        if ($phase === 'pages') {
            return $this->importPages((bool) $this->option('dry-run'));
        }

        $this->error('Unsupported or missing --phase. Supported: categories, users, petitions, petition-assets, signatures, signature-counts, pages');

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

    private function importPetitions(bool $dryRun): int
    {
        $chunk = $this->input->hasParameterOption('--chunk')
            ? max(1, (int) $this->option('chunk'))
            : 1000;

        $shortLocales = array_flip(config('language_flags', []));

        $categoryMap = [];
        foreach (DB::connection('legacy')->table('petition_categories')->select('id', 'old_id')->get() as $c) {
            $categoryMap[(int) $c->id] = (int) $c->old_id;
        }
        $targetCategoryIds = array_flip(
            DB::table('categories')->pluck('id')->map(fn ($v) => (int) $v)->all()
        );

        $minId = $this->option('from-id') !== null
            ? (int) $this->option('from-id')
            : (int) DB::connection('legacy')->table('petitions')->min('id');

        $maxId = $this->option('to-id') !== null
            ? (int) $this->option('to-id')
            : (int) DB::connection('legacy')->table('petitions')->max('id');

        $processed = 0;
        $created = 0;
        $updated = 0;
        $skippedUsers = 0;
        $missingMapping = 0;
        $targetMissingCategory = 0;
        $collisions = 0;

        $usedSlugs = [];
        DB::table('petition_translations')
            ->where(function ($q) use ($minId, $maxId) {
                $q->where('petition_id', '<', $minId)->orWhere('petition_id', '>', $maxId);
            })
            ->select('petition_id', 'locale', 'slug')
            ->orderBy('petition_id')
            ->chunk(5000, function ($chunkRows) use (&$usedSlugs) {
                foreach ($chunkRows as $t) {
                    $usedSlugs[$t->locale][$t->slug] = (int) $t->petition_id;
                }
            });

        for ($lo = $minId; $lo <= $maxId; $lo += $chunk) {
            $hi = min($lo + $chunk - 1, $maxId);

            $rows = DB::connection('legacy')->table('petitions')
                ->where('id', '>=', $lo)
                ->where('id', '<=', $hi)
                ->orderBy('id')
                ->get();

            if ($rows->isEmpty()) {
                continue;
            }

            $ids = $rows->pluck('id')->map(fn ($v) => (int) $v)->all();
            $userIds = $rows->pluck('user_id')->map(fn ($v) => (int) $v)->unique()->values()->all();

            $existingUserIds = array_flip(
                DB::table('users')->whereIn('id', $userIds)->pluck('id')->map(fn ($v) => (int) $v)->all()
            );
            $existingPetitionIds = array_flip(
                DB::table('petitions')->whereIn('id', $ids)->pluck('id')->map(fn ($v) => (int) $v)->all()
            );

            $petitionRecords = [];
            $translationRecords = [];

            foreach ($rows as $row) {
                $processed++;

                if (! isset($existingUserIds[(int) $row->user_id])) {
                    $skippedUsers++;

                    continue;
                }

                $categoryId = null;
                if ((int) $row->category_id !== 0) {
                    if (! isset($categoryMap[(int) $row->category_id])) {
                        $missingMapping++;
                    } elseif (! isset($targetCategoryIds[$categoryMap[(int) $row->category_id]])) {
                        $targetMissingCategory++;
                    } else {
                        $categoryId = $categoryMap[(int) $row->category_id];
                    }
                }

                $published = ((string) $row->active === '1' && (string) $row->verified === '1');
                $imageUrl = (str_starts_with((string) $row->pic, 'http://') || str_starts_with((string) $row->pic, 'https://'))
                    ? $row->pic
                    : null;

                if (isset($existingPetitionIds[(int) $row->id])) {
                    $updated++;
                } else {
                    $created++;
                }

                $petitionRecords[] = [
                    'id' => (int) $row->id,
                    'user_id' => (int) $row->user_id,
                    'goal_signatures' => (int) $row->goal,
                    'signature_count' => 0,
                    'status' => $published ? 'published' : 'draft',
                    'category_id' => $categoryId,
                    'target' => $row->target,
                    'city' => $row->city,
                    'community' => $row->community,
                    'community_url' => $row->community_url,
                    'youtube_url' => $row->yt,
                    'image_url' => $imageUrl,
                    'views' => (int) $row->views,
                    'is_active' => ((string) $row->alive === '1'),
                    'is_featured' => ((string) $row->featured === '1'),
                    'created_at' => $row->dt,
                    'updated_at' => $row->dt,
                ];

                $locale = $shortLocales[$this->prefixLocales[$row->prefix] ?? 'en_US'] ?? 'en';
                $source = trim((string) $row->slug) !== '' ? $row->slug : $row->title;
                $slug = $this->uniquePetitionSlug($locale, (string) $source, (int) $row->id, $usedSlugs, $collisions);

                $translationRecords[] = [
                    'petition_id' => (int) $row->id,
                    'locale' => $locale,
                    'title' => $row->title,
                    'description' => $row->text,
                    'slug' => $slug,
                    'is_featured' => ((string) $row->featured === '1'),
                    'created_at' => $row->dt,
                    'updated_at' => $row->dt,
                ];
            }

            if ($dryRun || empty($petitionRecords)) {
                continue;
            }

            DB::transaction(function () use ($petitionRecords, $translationRecords) {
                foreach (array_chunk($petitionRecords, 1000) as $batch) {
                    DB::table('petitions')->upsert(
                        $batch,
                        ['id'],
                        ['user_id', 'goal_signatures', 'status', 'category_id', 'target', 'city', 'community', 'community_url', 'youtube_url', 'image_url', 'views', 'is_active', 'is_featured', 'updated_at']
                    );
                }

                foreach (array_chunk($translationRecords, 1000) as $batch) {
                    DB::table('petition_translations')->upsert(
                        $batch,
                        ['petition_id', 'locale'],
                        ['title', 'description', 'slug', 'is_featured', 'updated_at']
                    );
                }
            });
        }

        $this->info(($dryRun ? '[dry-run] ' : '').'Petition import complete.');
        $this->line("range: {$minId}-{$maxId} chunk: {$chunk} processed: {$processed}");
        $this->line("created: {$created} updated: {$updated}");
        $this->line("skipped (missing user): {$skippedUsers} missing mapping: {$missingMapping} target-missing category: {$targetMissingCategory} collisions: {$collisions}");

        return self::SUCCESS;
    }

    private function importPetitionAssets(bool $dryRun): int
    {
        $chunk = $this->input->hasParameterOption('--chunk')
            ? max(1, (int) $this->option('chunk'))
            : 500;

        $picsPath = env('LEGACY_PICS_PATH');
        $picsPath = is_string($picsPath) ? rtrim($picsPath, '/') : null;

        $minId = $this->option('from-id') !== null
            ? (int) $this->option('from-id')
            : (int) DB::connection('legacy')->table('petitions')->min('id');

        $maxId = $this->option('to-id') !== null
            ? (int) $this->option('to-id')
            : (int) DB::connection('legacy')->table('petitions')->max('id');

        $tagsUpdated = 0;
        $tagsSkippedMissing = 0;
        $imgCopied = 0;
        $imgReused = 0;
        $imgMissing = 0;
        $imgInvalid = 0;
        $imgSkipped = 0;

        for ($lo = $minId; $lo <= $maxId; $lo += $chunk) {
            $hi = min($lo + $chunk - 1, $maxId);

            $petitions = DB::connection('legacy')->table('petitions')
                ->where('id', '>=', $lo)->where('id', '<=', $hi)
                ->orderBy('id')
                ->get(['id', 'prefix', 'pic']);

            if ($petitions->isEmpty()) {
                continue;
            }

            $ids = $petitions->pluck('id')->map(fn ($v) => (int) $v)->all();
            $petitionPrefix = [];
            foreach ($petitions as $p) {
                $petitionPrefix[(int) $p->id] = $p->prefix;
            }

            $existingTargetIds = array_flip(
                DB::table('petitions')->whereIn('id', $ids)->pluck('id')->map(fn ($v) => (int) $v)->all()
            );

            $tagRows = DB::connection('legacy')->table('petitions_tags')
                ->join('tags', 'tags.id', '=', 'petitions_tags.tag_id')
                ->whereIn('petitions_tags.petition_id', $ids)
                ->orderBy('petitions_tags.petition_id')
                ->orderBy('tags.id')
                ->get(['petitions_tags.petition_id', 'petitions_tags.prefix', 'tags.name']);

            $grouped = [];
            foreach ($tagRows as $tr) {
                $pid = (int) $tr->petition_id;
                if (($petitionPrefix[$pid] ?? null) !== $tr->prefix) {
                    continue;
                }
                $name = trim((string) $tr->name);
                if ($name === '') {
                    continue;
                }
                $key = mb_strtolower($name);
                if (! isset($grouped[$pid])) {
                    $grouped[$pid] = [];
                }
                if (! isset($grouped[$pid][$key])) {
                    $grouped[$pid][$key] = $name;
                }
            }

            $tagUpdates = [];
            foreach ($petitions as $p) {
                $pid = (int) $p->id;
                if (isset($existingTargetIds[$pid])) {
                    $tagUpdates[$pid] = '';
                }
            }
            foreach ($grouped as $pid => $names) {
                if (! isset($existingTargetIds[$pid])) {
                    $tagsSkippedMissing++;

                    continue;
                }
                $tagUpdates[$pid] = implode(', ', array_values($names));
            }

            $coverUpdates = [];
            foreach ($petitions as $p) {
                $pid = (int) $p->id;
                $pic = (string) $p->pic;

                if ($pic === '' || str_starts_with($pic, 'http://') || str_starts_with($pic, 'https://')) {
                    $imgSkipped++;

                    continue;
                }

                if (! $this->validPicFilename($pic)) {
                    $imgInvalid++;

                    continue;
                }

                if (! isset($existingTargetIds[$pid])) {
                    $imgSkipped++;

                    continue;
                }

                $fullLocale = $this->prefixLocales[$p->prefix] ?? 'en_US';
                $relative = 'petitions/legacy/'.$fullLocale.'/'.$pic;

                if ($picsPath === null) {
                    $imgMissing++;

                    continue;
                }

                $source = $picsPath.'/'.$fullLocale.'/'.$pic;

                if (! is_file($source)) {
                    $imgMissing++;

                    continue;
                }

                $dest = Storage::disk('public')->path($relative);

                if (is_file($dest) && filesize($dest) === filesize($source) && hash_file('sha256', $dest) === hash_file('sha256', $source)) {
                    $imgReused++;
                    $coverUpdates[$pid] = $relative;

                    continue;
                }

                if ($dryRun) {
                    $imgCopied++;
                    $coverUpdates[$pid] = $relative;

                    continue;
                }

                if (! is_dir(dirname($dest))) {
                    mkdir(dirname($dest), 0755, true);
                }

                if (copy($source, $dest)) {
                    $imgCopied++;
                    $coverUpdates[$pid] = $relative;
                } else {
                    $imgMissing++;
                }
            }

            if ($dryRun) {
                continue;
            }

            $this->bulkUpdateColumn('tags', $tagUpdates);
            $this->bulkUpdateColumn('cover_image', $coverUpdates);
            $tagsUpdated += count($tagUpdates);
        }

        $this->info(($dryRun ? '[dry-run] ' : '').'Petition assets import complete.');
        $this->line("range: {$minId}-{$maxId} chunk: {$chunk}");
        $this->line("tags updated: {$tagsUpdated} tags skipped (missing target): {$tagsSkippedMissing}");
        $this->line("images copied: {$imgCopied} reused: {$imgReused} missing: {$imgMissing} invalid: {$imgInvalid} skipped: {$imgSkipped}");

        return self::SUCCESS;
    }

    private function bulkUpdateColumn(string $column, array $updates): void
    {
        $this->bulkUpdateColumnByTable('petitions', $column, $updates);
    }

    private function bulkUpdateColumnByTable(string $table, string $column, array $updates): void
    {
        if (empty($updates)) {
            return;
        }

        DB::transaction(function () use ($table, $column, $updates) {
            foreach (array_chunk($updates, 500, true) as $batch) {
                $ids = array_keys($batch);
                $cases = '';
                $bindings = [];
                foreach ($batch as $id => $value) {
                    $cases .= ' when ? then ?';
                    $bindings[] = $id;
                    $bindings[] = $value;
                }
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sql = "update `{$table}` set `{$column}` = case `id`{$cases} end where `id` in ({$placeholders})";
                DB::update($sql, array_merge($bindings, $ids));
            }
        });
    }

    private function validPicFilename(string $pic): bool
    {
        if ($pic === '' || str_contains($pic, '/') || str_contains($pic, '\\') || str_contains($pic, "\0")) {
            return false;
        }

        if ($pic === '.' || $pic === '..' || str_contains($pic, '..')) {
            return false;
        }

        return basename($pic) === $pic;
    }

    private function importSignatures(bool $dryRun): int
    {
        $chunk = $this->input->hasParameterOption('--chunk')
            ? max(1, (int) $this->option('chunk'))
            : 1000;

        $shortLocales = array_flip(config('language_flags', []));

        $minId = $this->option('from-id') !== null
            ? (int) $this->option('from-id')
            : (int) DB::connection('legacy')->table('signatures')->min('id');

        $maxId = $this->option('to-id') !== null
            ? (int) $this->option('to-id')
            : (int) DB::connection('legacy')->table('signatures')->max('id');

        $inserted = 0;
        $duplicates = 0;
        $missingPetition = 0;
        $missingUser = 0;
        $invalidTimestamp = 0;

        for ($lo = $minId; $lo <= $maxId; $lo += $chunk) {
            $hi = min($lo + $chunk - 1, $maxId);

            $rows = DB::connection('legacy')->table('signatures')
                ->where('id', '>=', $lo)->where('id', '<=', $hi)
                ->orderBy('id')
                ->get(['id', 'user_id', 'petition_id', 'verified', 'deleted', 'dt', 'text', 'ip', 'prefix']);

            if ($rows->isEmpty()) {
                continue;
            }

            $petitionIds = $rows->pluck('petition_id')->map(fn ($v) => (int) $v)->unique()->values()->all();
            $userIds = $rows->pluck('user_id')->map(fn ($v) => (int) $v)->unique()->values()->all();

            $existingPetitionIds = array_flip(
                DB::table('petitions')->whereIn('id', $petitionIds)->pluck('id')->map(fn ($v) => (int) $v)->all()
            );

            $targetUsers = [];
            foreach (DB::table('users')->whereIn('id', $userIds)->get(['id', 'name', 'first_name', 'nickname', 'email', 'identify_mode']) as $u) {
                $targetUsers[(int) $u->id] = $u;
            }

            $records = [];
            foreach ($rows as $row) {
                if (! isset($existingPetitionIds[(int) $row->petition_id])) {
                    $missingPetition++;

                    continue;
                }

                if (! isset($targetUsers[(int) $row->user_id])) {
                    $missingUser++;

                    continue;
                }

                $user = $targetUsers[(int) $row->user_id];
                $name = match ($user->identify_mode) {
                    'nick' => trim((string) $user->nickname) !== '' ? $user->nickname : $user->name,
                    'name' => trim((string) $user->first_name) !== '' ? $user->first_name : $user->name,
                    default => $user->name,
                };

                $dt = $row->dt;
                if ($dt === null || $dt === '0000-00-00 00:00:00') {
                    $dt = now();
                    $invalidTimestamp++;
                }

                $records[] = [
                    'id' => (int) $row->id,
                    'petition_id' => (int) $row->petition_id,
                    'user_id' => (int) $row->user_id,
                    'name' => $name,
                    'email' => $user->email,
                    'locale' => $shortLocales[$this->prefixLocales[$row->prefix] ?? 'en_US'] ?? 'en',
                    'text' => $row->text,
                    'confirmed' => ((string) $row->verified === '1' && (string) $row->deleted === '0'),
                    'is_spam' => false,
                    'ip_address' => $row->ip,
                    'created_at' => $dt,
                    'updated_at' => $dt,
                ];
            }

            if ($dryRun || empty($records)) {
                continue;
            }

            DB::transaction(function () use ($records, &$inserted, &$duplicates) {
                foreach (array_chunk($records, 500) as $batch) {
                    $affected = DB::table('signatures')->insertOrIgnore($batch);
                    $inserted += $affected;
                    $duplicates += count($batch) - $affected;
                }
            });
        }

        $this->info(($dryRun ? '[dry-run] ' : '').'Signature import complete.');
        $this->line("range: {$minId}-{$maxId} chunk: {$chunk}");
        $this->line("inserted: {$inserted} duplicates: {$duplicates}");
        $this->line("missing petitions: {$missingPetition} missing users: {$missingUser} invalid timestamps: {$invalidTimestamp}");

        return self::SUCCESS;
    }

    private function rebuildSignatureCounts(bool $dryRun): int
    {
        $chunk = $this->input->hasParameterOption('--chunk')
            ? max(1, (int) $this->option('chunk'))
            : 1000;

        $minId = $this->option('from-id') !== null
            ? (int) $this->option('from-id')
            : (int) DB::table('petitions')->min('id');

        $maxId = $this->option('to-id') !== null
            ? (int) $this->option('to-id')
            : (int) DB::table('petitions')->max('id');

        if ($minId === 0 && $maxId === 0 && DB::table('petitions')->count() === 0) {
            $this->info(($dryRun ? '[dry-run] ' : '').'Signature count rebuild complete.');
            $this->line('range: empty processed: 0');
            $this->line('petitions updated: 0 confirmed signatures counted: 0 zero-count petitions: 0');

            return self::SUCCESS;
        }

        if ($minId > $maxId) {
            $this->error('Invalid range: from-id is greater than to-id.');

            return self::FAILURE;
        }

        $petitionsUpdated = 0;
        $confirmedCounted = 0;
        $zeroCount = 0;

        for ($lo = $minId; $lo <= $maxId; $lo += $chunk) {
            $hi = min($lo + $chunk - 1, $maxId);

            $ids = DB::table('petitions')
                ->where('id', '>=', $lo)->where('id', '<=', $hi)
                ->orderBy('id')
                ->pluck('id')->map(fn ($v) => (int) $v)->all();

            if (empty($ids)) {
                continue;
            }

            $counts = DB::table('signatures')
                ->whereIn('petition_id', $ids)
                ->where('confirmed', 1)
                ->groupBy('petition_id')
                ->selectRaw('petition_id, COUNT(*) AS c')
                ->pluck('c', 'petition_id')
                ->toArray();

            $updates = [];
            foreach ($ids as $id) {
                $count = (int) ($counts[$id] ?? 0);
                $updates[$id] = $count;
                $confirmedCounted += $count;
                if ($count === 0) {
                    $zeroCount++;
                }
            }

            $petitionsUpdated += count($updates);

            if ($dryRun) {
                continue;
            }

            $this->bulkUpdateColumn('signature_count', $updates);
        }

        $this->info(($dryRun ? '[dry-run] ' : '').'Signature count rebuild complete.');
        $this->line("range: {$minId}-{$maxId} chunk: {$chunk}");
        $this->line("petitions updated: {$petitionsUpdated} confirmed signatures counted: {$confirmedCounted} zero-count petitions: {$zeroCount}");

        return self::SUCCESS;
    }

    private function importPages(bool $dryRun): int
    {
        $chunk = $this->input->hasParameterOption('--chunk')
            ? max(1, (int) $this->option('chunk'))
            : 500;

        $shortLocales = array_flip(config('language_flags', []));

        $minId = $this->option('from-id') !== null
            ? (int) $this->option('from-id')
            : (int) DB::connection('legacy')->table('pages')->min('id');

        $maxId = $this->option('to-id') !== null
            ? (int) $this->option('to-id')
            : (int) DB::connection('legacy')->table('pages')->max('id');

        $pagesCreated = 0;
        $pagesUpdated = 0;
        $translationsCreated = 0;
        $translationsUpdated = 0;
        $collisions = 0;
        $invalidTimestamp = 0;

        $usedSlugs = [];
        DB::table('page_translations')
            ->where(function ($q) use ($minId, $maxId) {
                $q->where('page_id', '<', $minId)->orWhere('page_id', '>', $maxId);
            })
            ->select('page_id', 'locale', 'slug')
            ->orderBy('page_id')
            ->chunk(5000, function ($rows) use (&$usedSlugs) {
                foreach ($rows as $t) {
                    $usedSlugs[$t->locale][$t->slug] = (int) $t->page_id;
                }
            });

        for ($lo = $minId; $lo <= $maxId; $lo += $chunk) {
            $hi = min($lo + $chunk - 1, $maxId);

            $rows = DB::connection('legacy')->table('pages')
                ->where('id', '>=', $lo)->where('id', '<=', $hi)
                ->orderBy('id')
                ->get(['id', 'published', 'title', 'slug', 'text', 'dt', 'prefix']);

            if ($rows->isEmpty()) {
                continue;
            }

            $ids = $rows->pluck('id')->map(fn ($v) => (int) $v)->all();

            $existingPageIds = array_flip(
                DB::table('pages')->whereIn('id', $ids)->pluck('id')->map(fn ($v) => (int) $v)->all()
            );
            $existingTranslations = [];
            foreach (DB::table('page_translations')->whereIn('page_id', $ids)->get(['id', 'page_id', 'locale']) as $t) {
                $existingTranslations[(int) $t->page_id.':'.$t->locale] = (int) $t->id;
            }

            $pageRecords = [];
            $insertTranslations = [];
            $updateTranslations = [];

            foreach ($rows as $row) {
                $pid = (int) $row->id;

                $dt = $row->dt;
                if ($dt === null || $dt === '0000-00-00 00:00:00') {
                    $dt = now();
                    $invalidTimestamp++;
                }

                if (isset($existingPageIds[$pid])) {
                    $pagesUpdated++;
                } else {
                    $pagesCreated++;
                }
                $pageRecords[] = ['id' => $pid, 'created_at' => $dt, 'updated_at' => $dt];

                $locale = $shortLocales[$this->prefixLocales[$row->prefix] ?? 'en_US'] ?? 'en';
                $source = trim((string) $row->slug) !== '' ? $row->slug : $row->title;
                $slug = $this->uniquePetitionSlug($locale, (string) $source, $pid, $usedSlugs, $collisions);
                $published = ((string) $row->published === '1');

                $key = $pid.':'.$locale;
                if (isset($existingTranslations[$key])) {
                    $translationsUpdated++;
                    $updateTranslations[$existingTranslations[$key]] = [
                        'title' => $row->title,
                        'slug' => $slug,
                        'content' => $row->text,
                        'published' => $published,
                        'updated_at' => $dt,
                    ];
                } else {
                    $translationsCreated++;
                    $insertTranslations[] = [
                        'page_id' => $pid,
                        'locale' => $locale,
                        'title' => $row->title,
                        'slug' => $slug,
                        'content' => $row->text,
                        'published' => $published,
                        'created_at' => $dt,
                        'updated_at' => $dt,
                    ];
                }
            }

            if ($dryRun) {
                continue;
            }

            DB::transaction(function () use ($pageRecords, $insertTranslations, $updateTranslations) {
                foreach (array_chunk($pageRecords, 500) as $batch) {
                    DB::table('pages')->upsert($batch, ['id'], ['updated_at']);
                }

                foreach (array_chunk($insertTranslations, 500) as $batch) {
                    DB::table('page_translations')->insert($batch);
                }

                foreach (['title', 'slug', 'content', 'published', 'updated_at'] as $col) {
                    $map = [];
                    foreach ($updateTranslations as $id => $values) {
                        $map[$id] = $values[$col];
                    }
                    $this->bulkUpdateColumnByTable('page_translations', $col, $map);
                }
            });
        }

        $this->info(($dryRun ? '[dry-run] ' : '').'Page import complete.');
        $this->line("range: {$minId}-{$maxId} chunk: {$chunk}");
        $this->line("pages created: {$pagesCreated} updated: {$pagesUpdated} translations created: {$translationsCreated} updated: {$translationsUpdated}");
        $this->line("collisions: {$collisions} invalid timestamps: {$invalidTimestamp}");

        return self::SUCCESS;
    }

    private function uniquePetitionSlug(string $locale, string $source, int $petitionId, array &$usedSlugs, int &$collisions): string
    {
        $base = Str::slug($source);

        if ($base === '') {
            $base = 'petition';
        }

        if (! isset($usedSlugs[$locale][$base]) || $usedSlugs[$locale][$base] === $petitionId) {
            $usedSlugs[$locale][$base] = $petitionId;

            return $base;
        }

        $collisions++;
        $slug = $base.'-'.$petitionId;
        $i = 2;

        while (isset($usedSlugs[$locale][$slug]) && $usedSlugs[$locale][$slug] !== $petitionId) {
            $slug = $base.'-'.$petitionId.'-'.$i;
            $i++;
        }

        $usedSlugs[$locale][$slug] = $petitionId;

        return $slug;
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
