<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->string('guardado_original')->default('No')->change();
            $table->string('guardado_editado')->default('No')->change();
        });
    }

    public function down(): void
    {
        DB::table('youtube_json_trackers')->whereNotIn('guardado_original', ['si', 'no'])->update(['guardado_original' => 'no']);
        DB::table('youtube_json_trackers')->whereNotIn('guardado_editado', ['si', 'no'])->update(['guardado_editado' => 'no']);

        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->enum('guardado_original', ['si', 'no'])->change();
            $table->enum('guardado_editado', ['si', 'no'])->change();
        });
    }
};
