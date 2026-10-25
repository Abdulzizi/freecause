<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy short links were bare "/{shortslug}" paths (6 chars, [0-9A-Z]).
        // They still exist on printed material and old embeds, so they get a
        // permanent redirect to the canonical petition URL.
        Schema::create('legacy_shortlinks', function (Blueprint $table) {
            $table->char('short_link', 6)->primary();
            $table->unsignedBigInteger('petition_id')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_shortlinks');
    }
};
