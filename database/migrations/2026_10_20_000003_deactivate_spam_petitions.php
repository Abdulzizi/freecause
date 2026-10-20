<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ids = $this->ids();

        if ($ids === []) {
            return;
        }

        $pids = DB::table('petition_translations')
            ->whereIn('id', $ids)
            ->where('locale', 'en')
            ->pluck('petition_id')
            ->unique()
            ->all();

        if ($pids === []) {
            return;
        }

        DB::table('petitions')
            ->whereIn('id', $pids)
            ->where('signature_count', 0)
            ->update(['is_active' => 0, 'status' => 'draft']);
    }

    public function down(): void
    {
        $ids = $this->ids();

        if ($ids === []) {
            return;
        }

        $pids = DB::table('petition_translations')
            ->whereIn('id', $ids)
            ->where('locale', 'en')
            ->pluck('petition_id')
            ->unique()
            ->all();

        if ($pids === []) {
            return;
        }

        DB::table('petitions')
            ->whereIn('id', $pids)
            ->update(['is_active' => 1, 'status' => 'published']);
    }

    private function ids(): array
    {
        $path = database_path('data/legacy_spam_translation_ids.txt');

        if (! is_file($path)) {
            return [];
        }

        return array_values(array_filter(array_map(
            'intval',
            explode(',', trim((string) file_get_contents($path)))
        )));
    }
};
