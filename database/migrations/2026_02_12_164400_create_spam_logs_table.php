<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spam_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->index();
            $table->string('ip', 45)->index();
            $table->text('payload')->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spam_logs');
    }
};
