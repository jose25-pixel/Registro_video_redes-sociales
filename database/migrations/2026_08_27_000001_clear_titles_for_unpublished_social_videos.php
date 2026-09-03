<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('video_social_publications')
            ->where('publicado', 'no')
            ->update(['titulo' => null]);
    }

    public function down(): void
    {
    }
};