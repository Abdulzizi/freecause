<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
