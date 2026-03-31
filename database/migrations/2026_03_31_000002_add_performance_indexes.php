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
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropIndex('petitions_status_active_created_index');
            $table->dropIndex('petitions_user_id_index');
        });

        Schema::table('signatures', function (Blueprint $table) {
            $table->dropIndex('signatures_confirmed_index');
        });
    }
};
