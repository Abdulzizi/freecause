<?php

namespace App\Console\Commands;

use App\Models\Petition;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Output\OutputInterface;

class PruneYoutubeLinks extends Command
{
    protected $signature = 'petitions:prune-youtube
        {--sleep=150 : Milliseconds to sleep between YouTube oEmbed requests}
        {--dry-run : Report what would be cleared without changing anything}';

    protected $description = 'Clear petition YouTube values that do not parse or point to an unavailable video';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $sleepMs = max(0, (int) $this->option('sleep'));
        $counts = ['kept' => 0, 'junk' => 0, 'unavailable' => 0, 'unchecked' => 0];
        $backup = $dryRun ? null : storage_path('app/prune-youtube-'.now()->format('Ymd-His').'.tsv');
        $rateLimited = false;
        $writeFailed = false;

        // Open the backup before touching any row, so a cleared value is never lost.
        $handle = $backup === null ? null : @fopen($backup, 'a');
        if ($handle === false) {
            $this->error('Cannot open the backup file '.$backup.'; nothing was changed.');

            return self::FAILURE;
        }

        DB::table('petitions')->whereNotNull('youtube_url')->where('youtube_url', '!=', '')
            ->select(['id', 'youtube_url'])
            ->chunkById(200, function ($rows) use ($dryRun, $sleepMs, $handle, &$counts, &$rateLimited, &$writeFailed) {
                foreach ($rows as $row) {
                    $id = Petition::youtubeId($row->youtube_url);
                    $reason = $id === null ? 'junk' : $this->oembedStatus($id, $sleepMs);

                    if ($reason === 'rate-limited') {
                        $rateLimited = true;

                        return false;
                    }

                    if ($reason === 'kept' || $reason === 'unchecked') {
                        $counts[$reason]++;

                        continue;
                    }

                    // Only clear the value that was read, so a link edited during the run is left for the next run.
                    if (! $dryRun && DB::table('petitions')->where('id', $row->id)->where('youtube_url', $row->youtube_url)
                        ->update(['youtube_url' => null]) !== 1) {
                        $counts['unchecked']++;

                        continue;
                    }

                    $counts[$reason]++;
                    // One line per cleared row, so the output and the backup file keep the old values.
                    $line = ($dryRun ? 'would clear' : 'cleared')."\t{$reason}\t{$row->id}\t"
                        .json_encode($row->youtube_url, JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_SLASHES);
                    $this->output->writeln($line, OutputInterface::OUTPUT_RAW);
                    if ($handle !== null && @fwrite($handle, $line."\n") !== strlen($line) + 1) {
                        $writeFailed = true;

                        return false;
                    }
                }
            });

        if ($handle !== null) {
            fclose($handle);
            if (filesize($backup) === 0) {
                unlink($backup);
            }
        }

        if ($writeFailed) {
            $this->error('Writing the backup file failed: stopped. The last cleared value is in the console output above.');
        }

        // No edge purge: show pages expire at the edge TTL, and purging every cleared petition would send hundreds of Cloudflare batches.
        if ($rateLimited) {
            $this->error('Rate limited by YouTube (429): stopped early, rerun later. Rows already cleared stay cleared.');
        }

        $this->info(($dryRun ? 'dry run: ' : '')
            .'kept '.$counts['kept']
            .', cleared junk '.$counts['junk']
            .', cleared unavailable '.$counts['unavailable']
            .', unchecked '.$counts['unchecked']);

        if ($backup !== null && is_file($backup)) {
            $this->info('Backup of cleared values: '.$backup);
        }

        return $rateLimited || $writeFailed ? self::FAILURE : self::SUCCESS;
    }

    private function oembedStatus(string $id, int $sleepMs): string
    {
        if ($sleepMs > 0) {
            usleep($sleepMs * 1000);
        }

        try {
            $status = Http::timeout(10)->get('https://www.youtube.com/oembed', [
                'format' => 'json',
                'url' => 'https://www.youtube.com/watch?v='.$id,
            ])->status();
        } catch (ConnectionException) {
            return 'unchecked';
        }

        return match (true) {
            $status === 200 => 'kept',
            $status === 429 => 'rate-limited',
            in_array($status, [400, 401, 403, 404], true) => 'unavailable',
            default => 'unchecked',
        };
    }
}
