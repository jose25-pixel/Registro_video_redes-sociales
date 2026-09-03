<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->string('ruta_ubicacion_editado')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->string('ruta_ubicacion_editado')->nullable(false)->change();
        });
    }
};
