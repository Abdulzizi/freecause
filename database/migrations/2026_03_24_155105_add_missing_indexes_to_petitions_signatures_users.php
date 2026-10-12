<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            if (! Schema::hasIndex('petitions', 'petitions_category_id_index')) {
                $table->index('category_id');
            }
        });

        Schema::table('signatures', function (Blueprint $table) {
            if (! Schema::hasIndex('signatures', 'signatures_user_id_index')) {
                $table->index('user_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasIndex('users', 'users_level_id_index')) {
                $table->index('level_id');
            }
        });
    }

    public function down(): void
    {
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

        if ($canDropFkIndex('petitions', 'category_id', 'petitions_category_id_index')) {
            Schema::table('petitions', function (Blueprint $table) {
                $table->dropIndex('petitions_category_id_index');
            });
        }

        if ($canDropFkIndex('signatures', 'user_id', 'signatures_user_id_index')) {
            Schema::table('signatures', function (Blueprint $table) {
                $table->dropIndex('signatures_user_id_index');
            });
        }

        if ($canDropFkIndex('users', 'level_id', 'users_level_id_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_level_id_index');
            });
        }
    }
};
