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
            $table->string('tipo_aparato')->nullable()->change();
            $table->string('marca')->nullable()->change();
            $table->string('modelo_comercial')->nullable()->change();
            $table->string('modelo')->nullable()->change();
            $table->string('tecnologia')->nullable()->change();
            $table->string('tema_error')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('youtube_json_trackers')->whereNull('tipo_aparato')->update(['tipo_aparato' => 'No aplica']);
        DB::table('youtube_json_trackers')->whereNull('marca')->update(['marca' => 'No aplica']);
        DB::table('youtube_json_trackers')->whereNull('modelo_comercial')->update(['modelo_comercial' => 'No aplica']);
        DB::table('youtube_json_trackers')->whereNull('modelo')->update(['modelo' => 'No aplica']);
        DB::table('youtube_json_trackers')->whereNull('tecnologia')->update(['tecnologia' => 'No aplica']);
        DB::table('youtube_json_trackers')->whereNull('tema_error')->update(['tema_error' => 'No aplica']);

        Schema::table('youtube_json_trackers', function (Blueprint $table) {
            $table->string('tipo_aparato')->nullable(false)->change();
            $table->string('marca')->nullable(false)->change();
            $table->string('modelo_comercial')->nullable(false)->change();
            $table->string('modelo')->nullable(false)->change();
            $table->string('tecnologia')->nullable(false)->change();
            $table->string('tema_error')->nullable(false)->change();
        });
    }
};
