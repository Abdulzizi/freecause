<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-panel filter indexes. Without these, the corresponding admin filters
 * full-scan prod-scale tables (users 18.45M / signatures 18.65M rows):
 *
 *  - users.nickname           → nickname prefix filter (AdminUsersController)
 *  - signatures.ip_address    → IP prefix filter (AdminSignaturesController)
 *  - signatures (is_spam, id) → standalone spam filter without a petition scope
 *  - logs.context             → context equality filter (AdminLogsController)
 *
 * CREATE INDEX on these sizes takes seconds-to-minutes on MySQL; run
 * `php artisan migrate --force` in a maintenance window if needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('nickname');
        });

        Schema::table('signatures', function (Blueprint $table) {
            $table->index('ip_address');
            $table->index(['is_spam', 'id']);
        });

        Schema::table('logs', function (Blueprint $table) {
            $table->index('context');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['nickname']);
        });

        Schema::table('signatures', function (Blueprint $table) {
            $table->dropIndex(['ip_address']);
            $table->dropIndex(['is_spam', 'id']);
        });

        Schema::table('logs', function (Blueprint $table) {
            $table->dropIndex(['context']);
        });
    }
};
