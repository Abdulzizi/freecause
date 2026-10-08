<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class StampVersion extends Command
{
    protected $signature = 'app:stamp-version {version} {--commit=} {--release=}';

    protected $description = 'Record the deployed version for the /version endpoint';

    public function handle(): int
    {
        $payload = [
            'version' => (string) $this->argument('version'),
            'release' => (string) ($this->option('release') ?: $this->argument('version')),
            'commit' => $this->option('commit') ?: null,
            'deployed_at' => now()->toIso8601String(),
        ];

        $path = (string) config('app.version_file');

        File::ensureDirectoryExists(dirname($path));
        File::put(
            $path,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
        );

        $this->info('Stamped version '.$payload['release']);

        return self::SUCCESS;
    }
}
