<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('url');
            $table->timestamps();
            $table->unique(['user_id', 'url']);
        });

        Schema::create('playlist_video', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained('youtube_json_trackers')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['playlist_id', 'video_id']);
        });

        DB::table('youtube_json_trackers')
            ->whereNotNull('link_lista')
            ->where('link_lista', '!=', '')
            ->orderBy('id')
            ->chunkById(500, function ($videos): void {
                foreach ($videos as $video) {
                    $now = now();
                    DB::table('playlists')->upsert([[
                        'user_id' => $video->user_id,
                        'name' => $video->titulo_lista ?: $video->link_lista,
                        'url' => $video->link_lista,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]], ['user_id', 'url'], ['name', 'updated_at']);

                    $playlistId = DB::table('playlists')
                        ->where('user_id', $video->user_id)
                        ->where('url', $video->link_lista)
                        ->value('id');

                    DB::table('playlist_video')->insertOrIgnore([
                        'playlist_id' => $playlistId,
                        'video_id' => $video->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('playlist_video');
        Schema::dropIfExists('playlists');
    }
};
