<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_social_publications', function (Blueprint $table) {
            $table->string('titulo')->nullable()->after('platform');
            $table->string('platform')->change();
        });
    }

    public function down(): void
    {
        Schema::table('video_social_publications', function (Blueprint $table) {
            $table->dropColumn('titulo');
            $table->enum('platform', ['facebook', 'tiktok'])->change();
        });
    }
};