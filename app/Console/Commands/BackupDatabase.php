<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=30 : Number of backups to retain}';

    protected $description = 'Create a database backup and prune old backups';

    public function handle(): int
    {
        $keep = (int) $this->option('keep');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sql.gz";
        $disk = 'backups';

        if (! Storage::disk($disk)->exists('/')) {
            Storage::disk($disk)->makeDirectory('/');
        }

        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $tempPath = storage_path("app/backups/{$filename}");

        $cmd = sprintf(
            'mysqldump --no-tablespaces -h%s -P%s -u%s %s %s | gzip > %s 2>/dev/null',
            escapeshellarg($dbHost),
            escapeshellarg((string) $dbPort),
            escapeshellarg($dbUser),
            $dbPass ? '-p'.escapeshellarg($dbPass) : '',
            escapeshellarg($dbName),
            escapeshellarg($tempPath)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || ! file_exists($tempPath)) {
            $this->error('Backup failed.');
            Log::error('Database backup failed');

            return 1;
        }

        $size = filesize($tempPath);
        $this->info("Backup created: {$filename} (".number_format($size / 1024 / 1024, 2).' MB)');

        Storage::disk($disk)->put($filename, file_get_contents($tempPath));
        unlink($tempPath);

        $this->pruneOldBackups($disk, $keep, $filename);

        Log::info("Database backup created: {$filename}", ['size' => $size]);
        $this->info('Backup complete.');

        return 0;
    }

    private function pruneOldBackups(string $disk, int $keep, string $currentBackup): void
    {
        $files = Storage::disk($disk)->files('/');
        $backups = array_filter($files, fn ($f) => str_starts_with(basename($f), 'backup_') && str_ends_with($f, '.sql.gz'));

        usort($backups, function ($a, $b) {
            return Storage::disk($disk)->lastModified($b) <=> Storage::disk($disk)->lastModified($a);
        });

        $toDelete = array_slice($backups, $keep);

        foreach ($toDelete as $file) {
            Storage::disk($disk)->delete($file);
            $this->line('Pruned old backup: '.basename($file));
        }
    }
}
