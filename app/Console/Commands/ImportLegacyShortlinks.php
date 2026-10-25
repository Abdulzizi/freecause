<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLegacyShortlinks extends Command
{
    protected $signature = 'legacy:import-shortlinks {file : CSV or .gz with "short_link,petition_id" rows} {--dry-run}';

    protected $description = 'Import legacy six-character short links so they 301 to the canonical petition URL';

    public function handle(): int
    {
        $path = (string) $this->argument('file');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $handle = str_ends_with($path, '.gz') ? gzopen($path, 'rb') : fopen($path, 'rb');
        if (! $handle) {
            $this->error("Cannot open: {$path}");

            return self::FAILURE;
        }

        $batch = [];
        $valid = 0;
        $invalid = 0;
        $imported = 0;
        $dry = (bool) $this->option('dry-run');

        $flush = function () use (&$batch, &$imported, $dry): void {
            if ($batch === [] || $dry) {
                return;
            }
            $imported += DB::table('legacy_shortlinks')->insertOrIgnore($batch);
            $batch = [];
        };

        while (($line = fgets($handle)) !== false) {
            $parts = explode(',', trim($line));
            if (count($parts) !== 2) {
                $invalid++;

                continue;
            }
            [$short, $petition] = [strtoupper(trim($parts[0])), trim($parts[1])];
            if (! preg_match('/^[0-9A-Z]{6}$/', $short) || ! ctype_digit($petition)) {
                $invalid++;

                continue;
            }
            $batch[] = ['short_link' => $short, 'petition_id' => (int) $petition];
            $valid++;

            if (count($batch) >= 1000) {
                $flush();
            }
        }

        $flush();
        str_ends_with($path, '.gz') ? gzclose($handle) : fclose($handle);

        $this->info(($dry ? '[dry-run] ' : '')."valid={$valid} invalid={$invalid} imported={$imported}");

        return self::SUCCESS;
    }
}
