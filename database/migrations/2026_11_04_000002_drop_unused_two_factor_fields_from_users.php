<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 2FA was never implemented; nothing reads or writes these columns.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'two_factor_secret')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['two_factor_secret', 'two_factor_enabled', 'two_factor_confirmed_at']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'two_factor_secret')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('two_factor_secret', 255)->nullable()->after('password_changed_at');
                $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_enabled');
            });
        }
    }
};
