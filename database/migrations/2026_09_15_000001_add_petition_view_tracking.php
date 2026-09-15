<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('petitions', 'views')) {
            Schema::table('petitions', function (Blueprint $table) {
                $table->unsignedBigInteger('views')->default(0)->after('signature_count');
            });
        }

        if (! Schema::hasTable('petition_views')) {
            Schema::create('petition_views', function (Blueprint $table) {
                $table->id();
                $table->foreignId('petition_id')->constrained()->cascadeOnDelete();
                $table->date('viewed_on');
                $table->unsignedBigInteger('views')->default(0);
                $table->timestamps();
                $table->unique(['petition_id', 'viewed_on']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('petition_views');

        if (Schema::hasColumn('petitions', 'views')) {
            Schema::table('petitions', function (Blueprint $table) {
                $table->dropColumn('views');
            });
        }
    }
};
