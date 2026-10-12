<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasIndex('users', 'users_level_index')) {
                $table->dropIndex('users_level_index');
            }

            $table->dropColumn('level');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('level', 30)->default('user')->after('ip');
            $table->index('level');
        });
    }
};
