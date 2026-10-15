<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Legacy parity: register seeded sig_options = empty(username) ? NAME_SURNAME : USERNAME,
// so a user who set a nickname defaulted to DISPLAYING it. The rewrite shipped later with
// identify_mode defaulting to 'full' and never seeded it on register (E2), so pre-fix users
// who typed a nickname are stuck on 'full' and still show their real name on signatures.
// Flip those users to 'nick' once, retroactively applying the legacy register-time default.
return new class extends Migration
{
    private const CHUNK = 1000;

    public function up(): void
    {
        // only users still on the DEFAULT 'full' who actually have a nickname.
        // never touch 'name' or already-'nick' rows -- those are explicit choices.
        $base = DB::table('users')
            ->where('identify_mode', 'full')
            ->whereNotNull('nickname')
            ->where('nickname', '<>', '');

        $lastId = 0;

        while (true) {
            $ids = (clone $base)
                ->where('id', '>', $lastId)
                ->orderBy('id')
                ->limit(self::CHUNK)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            DB::table('users')->whereIn('id', $ids)->update(['identify_mode' => 'nick']);

            $lastId = $ids->last();
        }
    }

    public function down(): void
    {
        // reverse: nickname-having 'nick' users back to 'full'.
        // cannot distinguish backfilled rows from genuine 'nick' choices, so this
        // reverts every nickname-set 'nick' user -- acceptable for a rollback.
        $base = DB::table('users')
            ->where('identify_mode', 'nick')
            ->whereNotNull('nickname')
            ->where('nickname', '<>', '');

        $lastId = 0;

        while (true) {
            $ids = (clone $base)
                ->where('id', '>', $lastId)
                ->orderBy('id')
                ->limit(self::CHUNK)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            DB::table('users')->whereIn('id', $ids)->update(['identify_mode' => 'full']);

            $lastId = $ids->last();
        }
    }
};
