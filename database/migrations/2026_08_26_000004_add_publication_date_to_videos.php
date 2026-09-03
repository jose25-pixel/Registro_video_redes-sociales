<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->date('fecha_publicacion')->nullable()->after('fecha_subido');
            $table->unsignedSmallInteger('anio')->nullable()->after('mes');
        });
    }

    public function down(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->dropColumn(['fecha_publicacion', 'anio']);
        });
    }
};