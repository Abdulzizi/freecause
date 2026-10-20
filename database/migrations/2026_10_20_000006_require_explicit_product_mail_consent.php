<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const FLAGS = ['notify_product', 'notify_new_signatures', 'notify_milestones'];

    public function up(): void
    {
        // The users table has ~18M rows, so a locking table copy stalls every
        // write for the whole deploy (it was killed once at 300s). Changing a
        // column default is metadata-only on MySQL, so run it explicitly with
        // ALGORITHM=INPLACE, LOCK=NONE.
        //
        // The portable Schema::change() path is deliberately NOT used: on
        // SQLite a column change rebuilds the table, and that rebuild demotes
        // the virtual users.email_domain column to a plain stored column, so
        // the admin domain search silently stops matching. The column default
        // is only a safety net for non-Eloquent inserts here; the User model
        // carries the authoritative default for every insert path.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            foreach (self::FLAGS as $flag) {
                DB::statement(
                    'ALTER TABLE `users` MODIFY `'.$flag.'` TINYINT(1) NOT NULL DEFAULT 0, '.
                    'ALGORITHM=INPLACE, LOCK=NONE'
                );
            }
        }

        // Opt-in must become the stored state, not just the column default:
        // every existing row was created with the flag defaulting to true and
        // no signup form ever asked for consent. Chunk by id so no single
        // statement holds a huge range of row locks.
        DB::table('users')
            ->where(function ($query): void {
                $query->where('notify_product', 1)
                    ->orWhere('notify_new_signatures', 1)
                    ->orWhere('notify_milestones', 1);
            })
            ->chunkById(10000, function ($users): void {
                DB::table('users')
                    ->whereIn('id', $users->pluck('id'))
                    ->update([
                        'notify_product' => 0,
                        'notify_new_signatures' => 0,
                        'notify_milestones' => 0,
                    ]);
            });
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            foreach (self::FLAGS as $flag) {
                DB::statement(
                    'ALTER TABLE `users` MODIFY `'.$flag.'` TINYINT(1) NOT NULL DEFAULT 1, '.
                    'ALGORITHM=INPLACE, LOCK=NONE'
                );
            }
        }

        // The stored rows are deliberately NOT restored. Putting them back to
        // 1 would re-subscribe ~18M accounts that never gave consent, which
        // is the exact condition that caused the SMTP2GO bounce report.
        // A rollback therefore leaves the flags off, and users can opt back in
        // individually from the preferences page.
    }
};
