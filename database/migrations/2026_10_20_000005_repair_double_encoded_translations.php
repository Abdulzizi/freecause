<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MARKERS = "(
        title LIKE BINARY '%Ã§%' OR title LIKE BINARY '%Ã£%' OR title LIKE BINARY '%Ã©%'
        OR title LIKE BINARY '%Ã³%' OR title LIKE BINARY '%Ã¡%' OR title LIKE BINARY '%Ã­%'
        OR title LIKE BINARY '%Ãº%' OR title LIKE BINARY '%Ã¢%' OR title LIKE BINARY '%Ãµ%'
        OR title LIKE BINARY '%Ã´%'
    )";

    private const MARKERS_DESC = "(
        description LIKE BINARY '%Ã§%' OR description LIKE BINARY '%Ã£%' OR description LIKE BINARY '%Ã©%'
        OR description LIKE BINARY '%Ã³%' OR description LIKE BINARY '%Ã¡%' OR description LIKE BINARY '%Ã­%'
        OR description LIKE BINARY '%Ãº%' OR description LIKE BINARY '%Ã¢%' OR description LIKE BINARY '%Ãµ%'
        OR description LIKE BINARY '%Ã´%'
    )";

    public function up(): void
    {
        $this->repair('title', self::MARKERS);
        $this->repair('description', self::MARKERS_DESC);
    }

    public function down(): void
    {
    }

    private function repair(string $column, string $markers): void
    {
        $ids = DB::select(
            'SELECT t.id FROM petition_translations t
             JOIN petitions p ON p.id = t.petition_id
             WHERE t.locale = ? AND NOT (p.is_active = 0 AND p.status = ?) AND '.$markers,
            ['en', 'draft']
        );

        foreach ($ids as $row) {
            $fixed = DB::selectOne(
                'SELECT CONVERT(BINARY CONVERT('.$column.' USING latin1) USING utf8mb4) AS t
                 FROM petition_translations WHERE id = ?',
                [$row->id]
            );

            if ($fixed === null || $fixed->t === null) {
                continue;
            }

            if (str_contains($fixed->t, 'Ã') || str_contains($fixed->t, 'Â')) {
                continue;
            }

            DB::table('petition_translations')->where('id', $row->id)->update([$column => $fixed->t]);
        }
    }
};
