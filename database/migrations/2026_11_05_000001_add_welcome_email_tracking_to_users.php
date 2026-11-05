<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Existing accounts are not enrolled in a retrospective welcome campaign.
            $table->boolean('welcome_email_pending')->default(false);
            $table->timestamp('welcome_email_sent_at')->nullable();
            $table->timestamp('preferred_language_confirmed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['welcome_email_pending', 'welcome_email_sent_at', 'preferred_language_confirmed_at']);
        });
    }
};
