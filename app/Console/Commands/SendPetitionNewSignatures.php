<?php

namespace App\Console\Commands;

use App\Mail\PetitionNewSignaturesMail;
use App\Models\Petition;
use App\Support\AppLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendPetitionNewSignatures extends Command
{
    protected $signature = 'emails:petition-new-signatures {--dry-run : Count petitions without sending}';

    protected $description = 'Send hourly batched new-signature notifications to petition owners';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $petitions = Petition::with('user')->whereNotNull('user_id')->orderBy('id')->get();

        $sent = 0;
        $skipped = 0;
        $counted = 0;

        foreach ($petitions as $petition) {
            $petitionId = $petition->id;
            $since = $petition->last_notified_at ?? now()->subHour();
            $total = DB::table('signatures')
                ->where('petition_id', $petitionId)
                ->where('confirmed', 1)
                ->where('created_at', '>=', $since)
                ->count();

            if ($total === 0) {
                continue;
            }

            $counted++;
            $owner = $petition->user;

            if (! $owner || ! $owner->wantsNewSignatureEmails()) {
                $skipped++;

                continue;
            }

            $signers = DB::table('signatures')
                ->where('petition_id', $petitionId)
                ->where('confirmed', 1)
                ->where('created_at', '>=', $since)
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
                DB::table('petitions')->where('id', $petitionId)->update(['last_notified_at' => now()]);
            }
            $sent++;
        }

        $summary = sprintf('petitions=%d sent=%d skipped=%d dry_run=%s', $counted, $sent, $skipped, $dryRun ? 'yes' : 'no');
        AppLog::info('New-signatures digest run', $summary);
        $this->info('New signatures: '.$summary);

        return self::SUCCESS;
    }
}
