<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // On MySQL a Doctrine/Blueprint ->change() emits a plain CHANGE COLUMN
        // that the server may execute as a locking table copy. On an ~18M-row
        // users table that stalls every write for the whole deploy (it was
        // killed once at 300s). A nullability-only change supports
        // ALGORITHM=INPLACE, LOCK=NONE, so run it explicitly there and keep
        // the portable Schema path for sqlite/other drivers (CI).
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement(
                'ALTER TABLE `users` MODIFY `password` VARCHAR(255) NULL, '.
                'ALGORITHM=INPLACE, LOCK=NONE'
            );

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement(
                'ALTER TABLE `users` MODIFY `password` VARCHAR(255) NOT NULL, '.
                'ALGORITHM=INPLACE, LOCK=NONE'
            );

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
        });
    }
};
