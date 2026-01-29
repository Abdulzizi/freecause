<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('petition_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');
            $table->string('email')->nullable();

            $table->timestamps();

            // prevent duplicate signing:
            // - logged-in users: unique per petition
            // - anonymous users: unique per email per petition
            $table->unique(['petition_id', 'user_id']);
            $table->unique(['petition_id', 'email']);

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
