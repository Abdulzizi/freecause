<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('users', 'users_notify_product_verified_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['notify_product', 'verified'], 'users_notify_product_verified_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('users', 'users_notify_product_verified_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_notify_product_verified_index');
            });
        }
    }
};
