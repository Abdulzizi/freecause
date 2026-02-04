<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('page', 60);          // e.g. 'home'
            $table->string('locale', 8);         // e.g. 'en', 'fr'
            $table->string('key', 80);           // e.g. 'hero_h1'
            $table->longText('value')->nullable(); // can contain HTML
            $table->timestamps();

            $table->unique(['page', 'locale', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_contents');
    }
};
