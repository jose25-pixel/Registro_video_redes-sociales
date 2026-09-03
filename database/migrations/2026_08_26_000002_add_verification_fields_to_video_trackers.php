<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->enum('youtube_formato_guardado', ['si', 'no'])->default('no')->after('descripcion');
        });

        Schema::table('video_social_publications', function (Blueprint $table) {
            $table->enum('formato_guardado', ['si', 'no'])->default('no')->after('formato');
        });
    }

    public function down(): void
    {
        Schema::table('video_social_publications', function (Blueprint $table) {
            $table->dropColumn('formato_guardado');
        });

        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->dropColumn('youtube_formato_guardado');
        });
    }
};