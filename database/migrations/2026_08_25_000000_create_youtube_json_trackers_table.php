<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youtube_json_trackers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Datos del aparato/producto
            $table->string('tipo_aparato');
            $table->string('marca');
            $table->decimal('kg', 8, 2);
            $table->string('modelo_comercial');
            $table->string('modelo');
            $table->string('tecnologia');
            $table->string('tema_error');

            // Guardado y ubicación de archivos
            $table->enum('guardado_original', ['si', 'no']);
            $table->string('ruta_ubicacion_original');
            $table->enum('guardado_editado', ['si', 'no']);
            $table->string('ruta_ubicacion_editado');

            // Datos de publicación principal (YouTube)
            $table->date('fecha_subido');
            $table->string('tipo_video');
            $table->string('titulo');
            $table->string('mes');
            $table->string('red_social');
            $table->string('formato');
            $table->string('youtube_link');
            $table->string('link_lista')->nullable();
            $table->enum('publicado_comunidad', ['si', 'no']);
            $table->text('descripcion');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youtube_json_trackers');
    }
};
