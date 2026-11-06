<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aggregate_traffic', function (Blueprint $table) {
            $table->dateTime('hour');
            $table->string('area', 16);
            $table->string('locale', 2);
            $table->string('device', 8);
            $table->string('kind', 10);
            $table->unsignedBigInteger('page_loads')->default(0);
            $table->unique(['hour', 'area', 'locale', 'device', 'kind'], 'aggregate_traffic_bucket');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aggregate_traffic');
    }
};
