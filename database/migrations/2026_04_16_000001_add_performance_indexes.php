<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spam_logs', function (Blueprint $table) {
            $table->index(['ip', 'type', 'created_at'], 'spam_logs_ip_type_created_index');
        });

        Schema::table('signatures', function (Blueprint $table) {
            $table->index(['petition_id', 'is_spam', 'created_at'], 'signatures_petition_spam_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('spam_logs', function (Blueprint $table) {
            $table->dropIndex('spam_logs_ip_type_created_index');
        });

        Schema::table('signatures', function (Blueprint $table) {
            $table->dropIndex('signatures_petition_spam_created_index');
        });
    }
};
