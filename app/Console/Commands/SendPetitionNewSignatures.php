<?php

namespace App\Console\Commands;

use App\Mail\PetitionNewSignaturesMail;
use App\Models\Petition;
use App\Support\AppLog;
use App\Support\MailQuota;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendPetitionNewSignatures extends Command
{
    protected $signature = 'emails:petition-new-signatures {--dry-run : Count petitions without sending}';

    protected $description = 'Send hourly batched new-signature notifications to petition owners';

    public function handle(): int
    {
        $lock = Cache::lock('new-sigs', 600);
        if (! $lock->get()) {
            $this->info('New signatures: already running');

            return self::SUCCESS;
        }

        try {
            return $this->runDigest();
        } finally {
            $lock->release();
        }
    }

    private function runDigest(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now();
        $fallback = (clone $cutoff)->subHour();

        if (! $dryRun && MailQuota::productPaused('petition_new_signatures_enabled')) {
            $this->warn('Product mail paused - new signature notifications skipped.');

            return self::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;
        $counted = 0;

        Petition::with('user')->whereNotNull('user_id')->orderBy('id')->chunkById(200, function ($petitions) use ($dryRun, $cutoff, $fallback, &$sent, &$skipped, &$counted) {
            $ids = $petitions->pluck('id')->all();
            $totals = DB::table('signatures')
                ->join('petitions', 'petitions.id', '=', 'signatures.petition_id')
                ->whereIn('signatures.petition_id', $ids)
                ->where('signatures.confirmed', 1)
                ->where('signatures.is_spam', 0)
                ->whereRaw('signatures.created_at > COALESCE(petitions.last_notified_at, ?)', [$fallback])
                ->where('signatures.created_at', '<=', $cutoff)
                ->select('signatures.petition_id as petition_id')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('signatures.petition_id')
                ->pluck('total', 'petition_id');

            foreach ($petitions as $petition) {
                $petitionId = $petition->id;
                $total = (int) ($totals[$petitionId] ?? 0);

                if ($total === 0) {
                    continue;
                }

                $counted++;
                $owner = $petition->user;

                if (! $owner || ! $owner->wantsNewSignatureEmails()) {
                    $skipped++;

                    continue;
                }

                $since = $petition->last_notified_at ?? $fallback;
                $signers = DB::table('signatures')
                    ->where('petition_id', $petitionId)
                    ->where('confirmed', 1)
                    ->where('is_spam', 0)
                    ->where('created_at', '>', $since)
                    ->where('created_at', '<=', $cutoff)
                    ->whereNotNull('user_id')
                    ->orderByDesc('id')
                    ->limit(3)
                    ->select(['name', 'text'])
                    ->get()
                    ->map(fn ($row) => [
                        'name' => $row->name,
                        'snippet' => Str::limit((string) $row->text, 140),
                    ]);

                if (! $dryRun) {
                    try {
                        Mail::to($owner->email)->queue(new PetitionNewSignaturesMail($petition, $owner, (int) $total, $signers));
                    } catch (\Throwable $e) {
                        AppLog::error('New-signatures email failed', 'Petition ID: '.$petitionId.' | '.$e->getMessage(), 'emails.new-signatures');
                        $skipped++;

                        continue;
                    }
                }

                if (! $dryRun) {
                    DB::afterCommit(fn () => DB::table('petitions')->where('id', $petitionId)->update(['last_notified_at' => $cutoff]));
                }
                $sent++;
            }
        });

        $summary = sprintf('petitions=%d sent=%d skipped=%d dry_run=%s', $counted, $sent, $skipped, $dryRun ? 'yes' : 'no');
        AppLog::info('New-signatures digest run', $summary);
        $this->info('New signatures: '.$summary);

        return self::SUCCESS;
    }
}
