<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            if (!collect(\DB::select("SHOW INDEX FROM signatures WHERE Key_name = 'signatures_created_at_index'"))->count()) {
                $table->index('created_at', 'signatures_created_at_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->dropIndex('signatures_created_at_index');
        });
    }
};
