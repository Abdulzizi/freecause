<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('users', 'users_name_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('users', 'users_name_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_name_index');
            });
        }
    }
};
