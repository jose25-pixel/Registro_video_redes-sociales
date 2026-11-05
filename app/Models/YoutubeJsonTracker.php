<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class YoutubeJsonTracker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'categoria',
        'subcategoria',
        'ubicacion',
        'especie',
        'tipo_aparato',
        'marca',
        'kg',
        'modelo_comercial',
        'modelo',
        'tecnologia',
        'tema_error',
        'guardado_original',
        'ruta_ubicacion_original',
        'guardado_editado',
        'ruta_ubicacion_editado',
        'fecha_subido',
        'fecha_publicacion',
        'tipo_video',
        'titulo',
        'mes',
        'anio',
        'red_social',
        'youtube_publicado',
        'formato',
        'youtube_link',
        'youtube_ruta_editado',
        'youtube_miniatura',
        'link_lista',
        'titulo_lista',
        'publicado_comunidad',
        'descripcion',
        'linea_tiempo',
        'youtube_formato_guardado',
    ];

    protected $casts = [
        'kg' => 'decimal:2',
        'fecha_subido' => 'date',
        'fecha_publicacion' => 'date',
    ];

    protected static function booted(): void
    {
        static::deleting(function (YoutubeJsonTracker $video) {
            if ($video->youtube_miniatura) {
                Storage::disk('public')->delete($video->youtube_miniatura);
            }
            foreach ($video->socialPublications as $social) {
                if ($social->miniatura) {
                    Storage::disk('public')->delete($social->miniatura);
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function socialPublications(): HasMany
    {
        return $this->hasMany(VideoSocialPublication::class);
    }

    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'playlist_video', 'video_id', 'playlist_id')
            ->withTimestamps();
    }
}
