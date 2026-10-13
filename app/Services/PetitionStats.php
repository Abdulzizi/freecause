<?php

namespace App\Services;

use App\Mail\PetitionMilestoneMail;
use App\Models\Petition;
use App\Models\User;
use App\Support\AppLog;
use App\Support\CacheVersion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PetitionStats
{
    public const MILESTONES = [100, 1000, 10000, 100000];

    public static function increment(int $petitionId, int $by = 1): void
    {
        self::incrementMany([$petitionId => $by]);
    }

    public static function notifyMilestoneIfCrossed(int $petitionId): void
    {
        try {
            $count = (int) DB::table('petitions')->where('id', $petitionId)->value('signature_count');

            if (! in_array($count, self::MILESTONES, true)) {
                return;
            }

            $petition = Petition::with('user')->find($petitionId);
            $owner = $petition?->user;

            if (! $owner instanceof User || ! $owner->wantsMilestoneEmails()) {
                return;
            }

            Mail::to($owner->email)->queue(new PetitionMilestoneMail($petition, $owner, $count));

            AppLog::info(
                'Milestone email queued',
                'Petition ID: '.$petitionId.' | Milestone: '.$count,
                'petition.milestone'
            );
        } catch (\Throwable $e) {
            AppLog::error(
                'Milestone email failed',
                'Petition ID: '.$petitionId.' | '.$e->getMessage(),
                'petition.milestone'
            );
        }
    }

    public static function incrementMany(array $byPetitionId): void
    {
        $touched = [];

        foreach ($byPetitionId as $id => $by) {
            $id = (int) $id;
            $by = (int) $by;

            if ($id <= 0 || $by <= 0) {
                continue;
            }

            DB::table('petitions')->where('id', $id)->increment('signature_count', $by);
            $touched[] = $id;
        }

        self::invalidate($touched);
    }

    public static function recalculate(int $petitionId): int
    {
        $count = DB::table('signatures')
            ->where('petition_id', $petitionId)
            ->where('confirmed', 1)
            ->count();

        DB::table('petitions')->where('id', $petitionId)->update(['signature_count' => $count]);

        self::invalidate([$petitionId]);

        return $count;
    }

    public static function recalculateMany(array $petitionIds): void
    {
        $touched = [];

        foreach (array_unique(array_map('intval', $petitionIds)) as $id) {
            if ($id <= 0) {
                continue;
            }

            $count = DB::table('signatures')
                ->where('petition_id', $id)
                ->where('confirmed', 1)
                ->count();

            DB::table('petitions')->where('id', $id)->update(['signature_count' => $count]);
            $touched[] = $id;
        }

        self::invalidate($touched);
    }

    private static function invalidate(array $petitionIds): void
    {
        if ($petitionIds === []) {
            return;
        }

        CacheVersion::bumpListings();

        foreach ($petitionIds as $id) {
            Cache::forget("petition:latest_sigs:{$id}");
        }
    }
}
