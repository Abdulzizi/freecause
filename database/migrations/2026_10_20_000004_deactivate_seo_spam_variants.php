<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pids = DB::table('petition_translations')
            ->whereIn('id', [24970, 24971, 25028, 25037])
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
        $pids = DB::table('petition_translations')
            ->whereIn('id', [24970, 24971, 25028, 25037])
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
};
