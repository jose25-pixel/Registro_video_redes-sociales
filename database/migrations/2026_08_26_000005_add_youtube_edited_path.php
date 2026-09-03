<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->string('youtube_ruta_editado')->nullable()->after('youtube_link');
            $table->string('tipo_video')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->dropColumn('youtube_ruta_editado');
            $table->string('tipo_video')->nullable(false)->change();
        });
    }
};