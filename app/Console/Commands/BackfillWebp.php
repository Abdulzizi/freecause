<?php

namespace App\Console\Commands;

use App\Jobs\GenerateWebpVariant;
use App\Support\ImageVariants;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackfillWebp extends Command
{
    protected $signature = 'images:backfill-webp
        {--sync : Process images inline instead of dispatching queue jobs}
        {--limit= : Cap the number of files to process}
        {--sleep=0 : Milliseconds to sleep between batches (queue) or files (sync)}
        {--dry-run : Count eligible files without processing or dispatching}';

    protected $description = 'Generate 800w/400w WebP variants for stored petition cover images';

    private const BASE_DIRS = ['petitions'];

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    private const CHUNK_SIZE = 100;

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $dryRun = (bool) $this->option('dry-run');
        $sync = (bool) $this->option('sync');
        $sleepMs = max(0, (int) $this->option('sleep'));

        $limit = $this->option('limit');
        $limit = ($limit === null || $limit === '') ? null : max(0, (int) $limit);

        $files = $this->collectFiles($disk);

        if ($files === null) {
            $this->warn('No petition image directories found. Nothing to backfill.');

            return 0;
        }

        if ($limit !== null) {
            $files = array_slice($files, 0, $limit);
        }

        $total = count($files);

        if ($total === 0) {
            $this->info('No eligible images found.');

            return 0;
        }

        $this->info(($dryRun ? '[dry-run] ' : '')."Found {$total} eligible image(s).");

        $skipped = 0;
        $processed = 0;
        $dispatched = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $chunk = [];

        foreach ($files as $rel) {
            if ($this->variantsExist($disk, $rel)) {
                $skipped++;
                $bar->advance();

                continue;
            }

            if ($dryRun) {
                $processed++;
                $bar->advance();

                continue;
            }

            if ($sync) {
                ImageVariants::ensure($disk->path($rel)) !== null ? $processed++ : $failed++;

                if ($sleepMs > 0) {
                    usleep($sleepMs * 1000);
                }
            } else {
                $chunk[] = $rel;

                if (count($chunk) >= self::CHUNK_SIZE) {
                    $dispatched += $this->dispatchChunk($chunk);
                    $chunk = [];

                    if ($sleepMs > 0) {
                        usleep($sleepMs * 1000);
                    }
                }
            }

            $bar->advance();
        }

        if (! $dryRun && ! $sync && $chunk !== []) {
            $dispatched += $this->dispatchChunk($chunk);
        }

        $bar->finish();
        $this->newLine(2);

        $this->line('  Total:                '.$total);

        if ($dryRun) {
            $this->line('  Would process:        '.$processed);
        } elseif ($sync) {
            $this->line('  Processed:            '.$processed);
        } else {
            $this->line('  Dispatched:           '.$dispatched);
        }

        $this->line('  Skipped (existing):   '.$skipped);
        $this->line('  Failed:               '.$failed);

        return 0;
    }

    /**
     * @return array<int, string>|null Relative paths, or null when no base dir exists.
     */
    private function collectFiles($disk): ?array
    {
        $found = false;
        $files = [];

        foreach (self::BASE_DIRS as $dir) {
            if (! $disk->directoryExists($dir)) {
                continue;
            }

            $found = true;

            foreach ($disk->allFiles($dir) as $file) {
                if ($this->isEligible($file)) {
                    $files[] = $file;
                }
            }
        }

        return $found ? $files : null;
    }

    private function isEligible(string $rel): bool
    {
        if (preg_match('/_[48]00w\.webp$/i', $rel)) {
            return false;
        }

        $extension = strtolower(pathinfo($rel, PATHINFO_EXTENSION));

        return in_array($extension, self::IMAGE_EXTENSIONS, true);
    }

    private function variantsExist($disk, string $rel): bool
    {
        $dir = dirname($rel);
        $dir = ($dir === '.' || $dir === '') ? '' : rtrim($dir, '/').'/';
        $base = basename($rel);
        $extension = strtolower(pathinfo($base, PATHINFO_EXTENSION));
        $stem = $extension === '' ? $base : substr($base, 0, -(strlen($extension) + 1));

        return $disk->exists($dir.$stem.'_800w.webp') && $disk->exists($dir.$stem.'_400w.webp');
    }

    /**
     * @param  array<int, string>  $rels
     */
    private function dispatchChunk(array $rels): int
    {
        foreach ($rels as $rel) {
            GenerateWebpVariant::dispatch($rel);
        }

        return count($rels);
    }
}
