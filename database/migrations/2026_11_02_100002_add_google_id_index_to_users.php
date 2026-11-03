<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('users', 'users_google_id_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('google_id', 'users_google_id_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('users', 'users_google_id_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_google_id_index');
            });
        }
    }
};
