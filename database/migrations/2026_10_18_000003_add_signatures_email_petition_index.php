<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            if (! Schema::hasIndex('signatures', 'signatures_email_petition')) {
                $table->index(['email', 'petition_id'], 'signatures_email_petition');
            }
        });
    }

    public function down(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->dropIndex('signatures_email_petition');
        });
    }
};
