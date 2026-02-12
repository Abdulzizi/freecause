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
        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('locale', 8);
            $table->string('title');
            $table->string('slug');
            $table->longText('content')->nullable();
            $table->boolean('published')->default(true);

            $table->timestamps();

            $table->unique(['locale', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_translations');
    }
};
