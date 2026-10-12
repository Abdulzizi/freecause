<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('petitions', 'petitions_status_active_sig_idx')) {
            Schema::table('petitions', function (Blueprint $table) {
                $table->index(['status', 'is_active', 'signature_count'], 'petitions_status_active_sig_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('petitions', 'petitions_status_active_sig_idx')) {
            Schema::table('petitions', function (Blueprint $table) {
                $table->dropIndex('petitions_status_active_sig_idx');
            });
        }
    }
};
