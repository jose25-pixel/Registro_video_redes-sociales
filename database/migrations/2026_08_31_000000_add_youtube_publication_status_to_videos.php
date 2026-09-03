<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->string('youtube_publicado')->default('si')->after('red_social');
            $table->date('fecha_subido')->nullable()->change();
            $table->string('titulo')->nullable()->change();
            $table->string('mes')->nullable()->change();
            $table->string('red_social')->nullable()->change();
            $table->string('formato')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->dropColumn('youtube_publicado');
        });
    }
};