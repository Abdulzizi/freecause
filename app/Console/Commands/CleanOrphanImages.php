<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanOrphanImages extends Command
{
    protected $signature = 'images:cleanup {--dry-run : List orphaned files without deleting them}';

    protected $description = 'Delete petition images in storage that are no longer referenced in the database';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $files = Storage::disk('public')->files('petitions');

        if (empty($files)) {
            $this->info('No files found in storage/petitions.');
            return 0;
        }

        $referenced = DB::table('petitions')
            ->whereNotNull('cover_image')
            ->where('cover_image', 'not like', 'http%')
            ->pluck('cover_image')
            ->toArray();

        $orphans = array_filter($files, fn($f) => ! in_array($f, $referenced, true));

        if (empty($orphans)) {
            $this->info('No orphan images found.');
            return 0;
        }

        $totalBytes = 0;

        foreach ($orphans as $file) {
            $size = Storage::disk('public')->size($file);
            $totalBytes += $size;

            if ($dryRun) {
                $this->line("[dry-run] would delete: {$file} (" . $this->formatBytes($size) . ')');
            } else {
                Storage::disk('public')->delete($file);
                $this->line("deleted: {$file} (" . $this->formatBytes($size) . ')');
            }
        }

        $label = $dryRun ? 'Would free' : 'Freed';
        $this->info(count($orphans) . " orphan file(s). {$label}: " . $this->formatBytes($totalBytes));

        return 0;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
