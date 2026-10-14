<?php

namespace App\Console\Commands;

use App\Mail\PetitionDigestMail;
use App\Models\Petition;
use App\Models\User;
use App\Support\AppLog;
use App\Support\Locale;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class SendPetitionDigest extends Command
{
    protected $signature = 'emails:petition-digest {--dry-run : Count recipients without sending} {--limit= : Maximum number of users to process}';

    protected $description = 'Send the petition digest to verified users who opted in to product emails';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(0, (int) $this->option('limit'));
        $defaultLocale = default_locale();

        $sent = 0;
        $skipped = 0;
        $failed = 0;
        $processed = 0;
        $seen = [];
        $petitionsByLocale = [];

        User::query()
            ->whereNotNull('email')
            ->where('verified', true)
            ->where('notify_product', true)
            ->whereNull('unsubscribed_at')
            ->chunkById(200, function ($users) use (
                $dryRun,
                $limit,
                $defaultLocale,
                &$sent,
                &$skipped,
                &$failed,
                &$processed,
                &$seen,
                &$petitionsByLocale
            ) {
                foreach ($users as $user) {
                    if ($limit > 0 && $processed >= $limit) {
                        return false;
                    }

                    $processed++;

                    if (isset($seen[$user->id])) {
                        continue;
                    }

                    $seen[$user->id] = true;

                    try {
                        $locale = Locale::toShort($user->locale ?: $defaultLocale);

                        if (! array_key_exists($locale, $petitionsByLocale)) {
                            $petitionsByLocale[$locale] = $this->recentPetitions($locale, $defaultLocale);
                        }

                        $petitions = $petitionsByLocale[$locale];

                        if ($petitions->isEmpty()) {
                            $skipped++;

                            continue;
                        }

                        if (! $dryRun) {
                            Mail::to($user->email)->queue(new PetitionDigestMail($user, $petitions));
                        }

                        $sent++;
                    } catch (\Throwable $e) {
                        $failed++;
                        AppLog::error('Petition digest failed', 'User ID: '.$user->id.' | '.$e->getMessage(), 'emails.digest');
                    }
                }

                return true;
            });

        $summary = sprintf(
            'processed=%d sent=%d skipped=%d failed=%d dry_run=%s',
            $processed,
            $sent,
            $skipped,
            $failed,
            $dryRun ? 'yes' : 'no'
        );

        AppLog::info('Petition digest run', $summary);
        $this->info('Petition digest: '.$summary);

        return self::SUCCESS;
    }

    private function recentPetitions(string $locale, string $defaultLocale): Collection
    {
        return Petition::query()
            ->select([
                'petitions.id',
                'petitions.signature_count',
                'petitions.created_at',
            ])
            ->translated($locale, 'pt_locale', 'pt_default', ['title' => 'tr_title', 'slug' => 'tr_slug'], $defaultLocale)
            ->where(function ($query) {
                $query->whereNotNull('pt_locale.title')
                    ->orWhereNotNull('pt_default.title');
            })
            ->where('petitions.status', 'published')
            ->where('petitions.is_active', 1)
            ->orderByDesc('petitions.created_at')
            ->limit(5)
            ->get();
    }
}
