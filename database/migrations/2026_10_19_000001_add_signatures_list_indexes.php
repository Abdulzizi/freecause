<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            if (! Schema::hasIndex('signatures', 'signatures_list_cover_index')) {
                $table->index(['petition_id', 'confirmed', 'is_spam', 'created_at'], 'signatures_list_cover_index');
            }
            if (! Schema::hasIndex('signatures', 'signatures_confirmed_created_index')) {
                $table->index(['confirmed', 'created_at'], 'signatures_confirmed_created_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->dropIndex('signatures_list_cover_index');
            $table->dropIndex('signatures_confirmed_created_index');
        });
    }
};
