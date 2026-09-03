<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->string('titulo_lista')->nullable()->after('link_lista');
        });
    }

    public function down(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->dropColumn('titulo_lista');
        });
    }
};