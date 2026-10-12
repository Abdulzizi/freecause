<?php

namespace App\Jobs;

use App\Support\ImageVariants;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateWebpVariant implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(private readonly string $relativePath) {}

    public function handle(): void
    {
        try {
            ImageVariants::ensure(Storage::disk('public')->path($this->relativePath));
        } catch (\Throwable $e) {
        }
    }
}
