<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->string('categoria')->default('Naturaleza')->after('user_id');
            $table->string('subcategoria')->nullable()->after('categoria');
            $table->string('ubicacion')->nullable()->after('subcategoria');
            $table->string('especie')->nullable()->after('ubicacion');
        });
    }

    public function down(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->dropColumn(['categoria', 'subcategoria', 'ubicacion', 'especie']);
        });
    }
};