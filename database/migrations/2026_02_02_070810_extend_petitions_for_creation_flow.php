<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            // metadata
            $table->string('target', 190)->nullable()->after('category_id');
            $table->string('tags', 255)->nullable()->after('target');
            $table->string('city', 120)->nullable()->after('tags');

            // community
            $table->string('community', 190)->nullable()->after('city');
            $table->string('community_url', 500)->nullable()->after('community');

            // media
            $table->string('youtube_url', 200)->nullable()->after('community_url');
            $table->string('image_url', 500)->nullable()->after('youtube_url');
        });
    }

    public function down(): void
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropColumn([
                'target',
                'tags',
                'city',
                'community',
                'community_url',
                'youtube_url',
                'image_url',
            ]);
        });
    }
};
