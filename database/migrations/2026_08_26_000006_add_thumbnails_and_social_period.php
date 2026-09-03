<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->string('youtube_miniatura')->nullable()->after('youtube_ruta_editado');
        });

        Schema::table('video_social_publications', function (Blueprint $table) {
            $table->unsignedSmallInteger('anio')->nullable()->after('fecha');
            $table->string('mes')->nullable()->after('anio');
            $table->string('miniatura')->nullable()->after('formato_guardado');
        });
    }

    public function down(): void
    {
        Schema::table('video_social_publications', function (Blueprint $table) {
            $table->dropColumn(['anio', 'mes', 'miniatura']);
        });

        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->dropColumn('youtube_miniatura');
        });
    }
};