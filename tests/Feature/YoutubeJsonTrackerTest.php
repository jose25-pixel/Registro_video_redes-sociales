<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\YoutubeJsonTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class YoutubeJsonTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_video(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        Storage::fake('public');

        $response = $this->actingAs($user)->post(route('videos.store'), array_merge($this->videoData(), [
            'youtube_miniatura' => UploadedFile::fake()->image('youtube.jpg'),
            'social' => [
                'facebook' => ['publicado' => 'si', 'tipo' => 'Video normal', 'fecha' => '2026-09-03', 'link' => 'https://www.facebook.com/video', 'miniatura' => UploadedFile::fake()->image('facebook.jpg')],
                'tiktok' => ['publicado' => 'no'],
            ],
        ]));

        $response->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('youtube_json_trackers', ['user_id' => $user->id, 'titulo' => 'Diagnostico de lavadora']);
        $this->assertDatabaseHas('youtube_json_trackers', ['titulo_lista' => 'Diagnosticos Samsung Wobble', 'link_lista' => 'https://www.youtube.com/playlist?list=abc123']);
        $this->assertDatabaseHas('youtube_json_trackers', ['mes' => 'septiembre', 'anio' => 2026]);
        $this->assertDatabaseHas('video_social_publications', ['platform' => 'facebook', 'publicado' => 'si', 'link' => 'https://www.facebook.com/video']);
        $this->assertDatabaseHas('video_social_publications', ['platform' => 'tiktok', 'publicado' => 'no']);
        $video = YoutubeJsonTracker::where('titulo', 'Diagnostico de lavadora')->firstOrFail();
        $facebook = $video->socialPublications()->where('platform', 'facebook')->firstOrFail();
        $this->assertSame('septiembre', $facebook->mes);
        $this->assertSame(2026, $facebook->anio);
        Storage::disk('public')->assertExists($video->youtube_miniatura);
        Storage::disk('public')->assertExists($facebook->miniatura);
    }

    public function test_video_form_rejects_invalid_required_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('videos.store'), ['titulo' => 'Sin datos']);

        $response->assertSessionHasErrors(['tipo_aparato', 'descripcion']);
        $this->assertDatabaseCount('youtube_json_trackers', 0);
    }

    public function test_video_can_be_created_without_youtube_link_and_social_defaults_to_no(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $data = $this->videoData();
        $data['youtube_link'] = '';
        unset($data['social']);

        $this->actingAs($user)->post(route('videos.store'), $data)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $video = YoutubeJsonTracker::where('titulo', $data['titulo'])->firstOrFail();
        $this->assertNull($video->youtube_link);
        $this->assertDatabaseHas('video_social_publications', [
            'youtube_json_tracker_id' => $video->id,
            'platform' => 'facebook',
            'publicado' => 'no',
        ]);
    }

    public function test_nature_video_can_be_saved_without_youtube_publication(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Naturaleza']);

        $this->actingAs($user)->post(route('videos.store'), [
            'subcategoria' => 'Plantas',
            'guardado_original' => 'No',
            'guardado_editado' => 'No',
            'youtube_publicado' => 'no',
            'descripcion' => 'Registro de una planta sin publicacion en YouTube.',
            'social' => ['facebook' => ['publicado' => 'no']],
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('youtube_json_trackers', [
            'user_id' => $user->id,
            'youtube_publicado' => 'no',
            'titulo' => null,
            'youtube_link' => null,
        ]);
    }

    public function test_user_cannot_edit_or_delete_another_users_video(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $video = YoutubeJsonTracker::factory()->for($owner)->create();

        $this->actingAs($otherUser)->get(route('videos.edit', $video))->assertNotFound();
        $this->actingAs($otherUser)->delete(route('videos.destroy', $video))->assertNotFound();
        $this->assertDatabaseHas('youtube_json_trackers', ['id' => $video->id]);
    }

    public function test_related_videos_are_grouped_by_model(): void
    {
        $user = User::factory()->create();
        $selectedVideo = YoutubeJsonTracker::factory()->for($user)->create(['modelo' => 'RT32', 'tipo_aparato' => 'Nevera', 'marca' => 'Samsung']);
        YoutubeJsonTracker::factory()->count(2)->for($user)->create(['modelo' => 'RT32', 'tipo_aparato' => 'Nevera', 'marca' => 'Samsung']);
        YoutubeJsonTracker::factory()->for($user)->create(['modelo' => 'RT35', 'tipo_aparato' => 'Nevera', 'marca' => 'Samsung']);

        $this->actingAs($user)->getJson(route('videos.related-search', [
            'video_id' => $selectedVideo->id,
            'modelo' => 'RT32',
            'tipo_aparato' => 'Nevera',
            'marca' => 'Samsung',
        ]))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.modelo', 'RT32')
            ->assertJsonCount(2, '0.videos');
    }

    public function test_related_videos_fall_back_to_same_brand_refrigerators(): void
    {
        $user = User::factory()->create();
        $selectedVideo = YoutubeJsonTracker::factory()->for($user)->create(['modelo' => 'WA90', 'tipo_aparato' => 'Lavadora', 'marca' => 'Samsung']);
        YoutubeJsonTracker::factory()->for($user)->create(['modelo' => 'RT32', 'tipo_aparato' => 'Nevera', 'marca' => 'Samsung']);
        YoutubeJsonTracker::factory()->for($user)->create(['modelo' => 'WFW5605', 'tipo_aparato' => 'Lavadora', 'marca' => 'Whirlpool']);

        $this->actingAs($user)->getJson(route('videos.related-search', [
            'video_id' => $selectedVideo->id,
            'modelo' => 'WA90',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Samsung',
        ]))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.modelo', 'RT32');
    }

    public function test_related_refrigerator_videos_fall_back_to_same_brand_washing_machines(): void
    {
        $user = User::factory()->create();
        $selectedVideo = YoutubeJsonTracker::factory()->for($user)->create(['modelo' => 'RT32', 'tipo_aparato' => 'Nevera', 'marca' => 'Samsung']);
        YoutubeJsonTracker::factory()->for($user)->create(['modelo' => 'WA90', 'tipo_aparato' => 'Lavadora', 'marca' => 'Samsung']);
        YoutubeJsonTracker::factory()->for($user)->create(['modelo' => 'RT35', 'tipo_aparato' => 'Nevera', 'marca' => 'Samsung']);

        $this->actingAs($user)->getJson(route('videos.related-search', [
            'video_id' => $selectedVideo->id,
            'modelo' => 'RT32',
            'tipo_aparato' => 'Nevera',
            'marca' => 'Samsung',
        ]))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.modelo', 'WA90');
    }

    public function test_automatic_references_prioritize_up_to_eight_videos_from_the_main_model(): void
    {
        $user = User::factory()->create();
        $latest = YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'Wobble 14',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Samsung',
            'tecnologia' => 'Wobble',
            'titulo' => 'Principal',
        ]);

        foreach (range(1, 8) as $number) {
            YoutubeJsonTracker::factory()->for($user)->create([
                'modelo' => 'Wobble 14',
                'tipo_aparato' => 'Lavadora',
                'marca' => 'Samsung',
                'tecnologia' => 'Wobble',
                'titulo' => "Principal {$number}",
                'youtube_link' => "https://youtube.com/watch?v=main{$number}",
            ]);
        }
        YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'Wobble 16',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Samsung',
            'tecnologia' => 'Wobble',
            'titulo' => 'Relacionado por tecnologia',
            'youtube_link' => 'https://youtube.com/watch?v=related',
        ]);

        $this->actingAs($user)->get(route('videos.index'))
            ->assertOk()
            ->assertViewHas('suggestions', function ($suggestions) use ($latest) {
                $byModel = $suggestions->groupBy('modelo');

                return $byModel->get('Wobble 14')->count() === 8
                    && ! $byModel->has('Wobble 16')
                    && ! $byModel->get('Wobble 14')->contains('id', $latest->id);
            });
    }

    public function test_automatic_references_complete_a_refrigerator_model_with_same_brand_related_appliances(): void
    {
        $user = User::factory()->create();
        $previousRefrigerator = YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'RT38',
            'tipo_aparato' => 'Nevera',
            'marca' => 'Samsung',
            'titulo' => 'Nevera Samsung anterior',
            'created_at' => now()->subMinutes(2),
        ]);
        YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'WA90',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Samsung',
            'titulo' => 'Lavadora Samsung relacionada',
            'created_at' => now()->subMinute(),
        ]);
        $latestRefrigerator = YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'RT38',
            'tipo_aparato' => 'Nevera',
            'marca' => 'Samsung',
            'titulo' => 'Nevera Samsung principal',
            'created_at' => now(),
        ]);

        $this->actingAs($user)->get(route('videos.index'))
            ->assertOk()
                ->assertViewHas('suggestions', function ($suggestions) use ($previousRefrigerator, $latestRefrigerator) {
                return $suggestions->pluck('modelo')->contains('RT38')
                    && $suggestions->pluck('modelo')->contains('WA90')
                    && $suggestions->contains('id', $previousRefrigerator->id)
                    && ! $suggestions->contains('id', $latestRefrigerator->id);
            });
    }

    private function videoData(): array
    {
        return [
            'categoria' => 'Naturaleza',
            'subcategoria' => 'Animales',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Samsung',
            'kg' => 18,
            'modelo_comercial' => 'EcoWash',
            'modelo' => 'WW18',
            'tecnologia' => 'Inverter',
            'tema_error' => 'No centrifuga',
            'guardado_original' => 'Verificar',
            'ruta_ubicacion_original' => 'D:/videos/originales/ww18.mp4',
            'guardado_editado' => 'Posible',
            'ruta_ubicacion_editado' => 'D:/videos/editados/ww18.mp4',
            'fecha_subido' => '2026-08-26',
            'fecha_publicacion' => '2026-09-02',
            'tipo_video' => 'Tutorial',
            'titulo' => 'Diagnostico de lavadora',
            'mes' => 'agosto',
            'red_social' => 'YouTube',
            'formato' => 'Short',
            'youtube_link' => 'https://www.youtube.com/watch?v=abc123',
            'youtube_ruta_editado' => 'D:/videos/youtube/editado.mp4',
            'titulo_lista' => 'Diagnosticos Samsung Wobble',
            'link_lista' => 'https://www.youtube.com/playlist?list=abc123',
            'publicado_comunidad' => 'no',
            'descripcion' => 'Revision del problema de centrifugado.',
            'youtube_formato_guardado' => 'si',
        ];
    }
}