<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_milestones')->default(true);
            $table->boolean('notify_product')->default(true);
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('unsubscribe_token', 64)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['unsubscribe_token']);
            $table->dropColumn(['notify_milestones', 'notify_product', 'unsubscribed_at', 'unsubscribe_token']);
        });
    }
};
