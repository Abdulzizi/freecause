<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petition_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petition_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title', 190);
            $table->string('slug', 220);
            $table->longText('description');
            $table->timestamps();

            $table->unique(['petition_id', 'locale']);
            $table->unique(['locale', 'slug']);

            $table->index(['petition_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petition_translations');
    }
};
