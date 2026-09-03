<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoSocialPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'youtube_json_tracker_id',
        'platform',
        'titulo',
        'publicado',
        'tipo',
        'fecha',
        'link',
        'formato',
        'formato_guardado',
        'anio',
        'mes',
        'miniatura',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(YoutubeJsonTracker::class, 'youtube_json_tracker_id');
    }
}
