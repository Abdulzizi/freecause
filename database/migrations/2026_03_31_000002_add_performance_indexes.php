<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->index(['status', 'is_active', 'created_at'], 'petitions_status_active_created_index');
            $table->index('user_id', 'petitions_user_id_index');
        });

        Schema::table('signatures', function (Blueprint $table) {
            $table->index('confirmed', 'signatures_confirmed_index');
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('petitions', 'petitions_status_active_created_index')) {
            Schema::table('petitions', function (Blueprint $table) {
                $table->dropIndex('petitions_status_active_created_index');
            });
        }

        $canDropFkIndex = function (string $table, string $column, string $index): bool {
            if (! Schema::hasIndex($table, $index)) {
                return false;
            }

            if (Schema::getConnection()->getDriverName() !== 'mysql') {
                return true;
            }

            $covered = Schema::getConnection()->selectOne(
                'SELECT COUNT(*) AS c FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND SEQ_IN_INDEX = 1
                   AND COLUMN_NAME = ? AND INDEX_NAME <> ?',
                [$table, $column, $index]
            );

            return ($covered->c ?? 0) > 0;
        };

        if ($canDropFkIndex('petitions', 'user_id', 'petitions_user_id_index')) {
            Schema::table('petitions', function (Blueprint $table) {
                $table->dropIndex('petitions_user_id_index');
            });
        }

        if (Schema::hasIndex('signatures', 'signatures_confirmed_index')) {
            Schema::table('signatures', function (Blueprint $table) {
                $table->dropIndex('signatures_confirmed_index');
            });
        }
    }
};
