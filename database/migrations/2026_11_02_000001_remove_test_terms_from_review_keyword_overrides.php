<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TEST_TERMS = ['adulzi', 'keyword'];

    public function up(): void
    {
        DB::table('settings')
            ->where('group', 'moderation')
            ->where('key', 'like', 'review_keywords%')
            ->orderBy('id')
            ->get(['id', 'key', 'value'])
            ->each(function ($row) {
                if ($row->key === 'review_keywords_disabled'
                    || str_starts_with($row->key, 'review_keywords_rescan')
                    || ! str_starts_with($row->key, 'review_keywords_')) {
                    return;
                }

                $entries = json_decode((string) $row->value, true);
                if (! is_array($entries)) {
                    return;
                }

                $kept = array_values(array_filter(
                    $entries,
                    fn ($entry) => ! is_array($entry) || ! in_array($entry['term'] ?? null, self::TEST_TERMS, true)
                ));

                if (count($kept) === count($entries)) {
                    return;
                }

                DB::table('settings')->where('id', $row->id)->update([
                    'value' => json_encode($kept, JSON_UNESCAPED_UNICODE),
                ]);
            });

        Cache::forget('settings:all');
        Cache::forget('review_keywords:rows');
    }

    public function down(): void {}
};
