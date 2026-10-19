<?php

namespace App\Console\Commands;

use App\Support\CacheVersion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileSignatureCounts extends Command
{
    protected $signature = 'signatures:reconcile {--dry-run : Show mismatches without fixing}';

    protected $description = 'Reconcile petition signature_count against actual confirmed signatures';

    public function handle(): int
    {
        $this->info('Reconciling signature counts...');

        $mismatches = DB::select('
            SELECT p.id, p.signature_count AS stored_count, sub.actual
            FROM petitions p
            JOIN (
                SELECT petition_id, COUNT(*) AS actual
                FROM signatures
                WHERE confirmed = 1 AND (is_spam = 0 OR is_spam IS NULL)
                GROUP BY petition_id
            ) sub ON sub.petition_id = p.id
            WHERE p.signature_count != sub.actual
            UNION
            SELECT p.id, p.signature_count AS stored_count, 0 AS actual
            FROM petitions p
            WHERE p.signature_count != 0
            AND NOT EXISTS (SELECT 1 FROM signatures s WHERE s.petition_id = p.id AND s.confirmed = 1 AND (s.is_spam = 0 OR s.is_spam IS NULL))
        ');

        if (empty($mismatches)) {
            $this->info('All signature counts are correct.');

            return 0;
        }

        $this->warn(count($mismatches).' petitions have mismatched signature counts.');

        if ($this->option('dry-run')) {
            $this->table(['Petition ID', 'Stored', 'Actual'], array_map(fn ($r) => [
                $r->id, $r->stored_count, $r->actual,
            ], $mismatches));

            return 0;
        }

        DB::update('
            UPDATE petitions p
            LEFT JOIN (
                SELECT petition_id, COUNT(*) AS actual
                FROM signatures
                WHERE confirmed = 1 AND (is_spam = 0 OR is_spam IS NULL)
                GROUP BY petition_id
            ) sub ON sub.petition_id = p.id
            SET p.signature_count = COALESCE(sub.actual, 0)
            WHERE p.signature_count != COALESCE(sub.actual, 0)
        ');

        CacheVersion::bumpListings();

        $this->info('Fixed '.count($mismatches).' petition signature counts.');

        return 0;
    }
}
