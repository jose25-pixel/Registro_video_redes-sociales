<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_social_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('youtube_json_tracker_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['facebook', 'tiktok']);
            $table->enum('publicado', ['si', 'no'])->default('no');
            $table->string('tipo')->nullable();
            $table->date('fecha')->nullable();
            $table->string('link')->nullable();
            $table->string('formato')->nullable();
            $table->timestamps();

            $table->unique(['youtube_json_tracker_id', 'platform'], 'video_social_pub_tracker_platform_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_social_publications');
    }
};
