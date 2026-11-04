<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ADDED = [
        ['term' => 'loan', 'concept' => 'loan'],
        ['term' => 'crypto', 'concept' => 'crypto'],
    ];

    public function up(): void
    {
        $row = DB::table('settings')
            ->where('group', 'moderation')
            ->where('key', 'review_keywords_en')
            ->first(['id', 'value']);

        if ($row !== null) {
            $entries = json_decode((string) $row->value, true);

            if (is_array($entries) && array_is_list($entries)) {
                $present = array_map(
                    fn ($entry) => is_array($entry) ? mb_strtolower((string) ($entry['term'] ?? '')) : '',
                    $entries
                );

                $missing = array_values(array_filter(
                    self::ADDED,
                    fn (array $entry) => ! in_array($entry['term'], $present, true)
                ));

                if ($missing !== []) {
                    DB::table('settings')->where('id', $row->id)->update([
                        'value' => json_encode(array_merge($entries, $missing), JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }
        }

        Cache::forget('settings:all');
        Cache::forget('review_keywords:rows');
    }

    public function down(): void {}
};
