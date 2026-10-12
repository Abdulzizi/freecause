<?php

namespace App\Jobs;

use App\Support\EdgeCachePurge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PurgeEdgeListings implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 3;

    public $backoff = [10, 30, 60];

    public int $uniqueFor = 60;

    public function __construct()
    {
        $this->afterCommit = true;
    }

    public function handle(): void
    {
        EdgeCachePurge::dispatchListingPurge();
    }
}
