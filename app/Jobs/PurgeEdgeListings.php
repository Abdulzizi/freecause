<?php

namespace App\Jobs;

use App\Support\EdgeCachePurge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Purges Cloudflare-edge listing URLs off the admin request path.
 * Runs on the queue workers (freecause-release-queue:* in prod); the
 * Cloudflare API round-trips no longer execute inside admin save requests.
 * afterCommit (from the Queueable trait) keeps purge semantics: the job is
 * only pushed once the triggering DB transaction commits.
 */
class PurgeEdgeListings implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 3;

    /** @var array<int, int> Sleep between attempts (seconds): 10s, 30s, 60s. */
    public $backoff = [10, 30, 60];

    public function __construct()
    {
        // Only push to the queue after the triggering DB transaction commits.
        $this->afterCommit = true;
    }

    public function handle(): void
    {
        EdgeCachePurge::dispatchListingPurge();
    }
}
