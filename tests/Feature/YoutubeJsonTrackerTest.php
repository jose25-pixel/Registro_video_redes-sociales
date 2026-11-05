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

        $response->assertSessionHasNoErrors()->assertRedirect(route('videos.index'));
        $this->assertDatabaseHas('youtube_json_trackers', ['user_id' => $user->id, 'titulo' => 'Diagnostico de lavadora']);
        $this->assertDatabaseHas('youtube_json_trackers', ['titulo_lista' => 'Diagnosticos Samsung Wobble', 'link_lista' => 'https://www.youtube.com/playlist?list=abc123']);
        $this->assertDatabaseHas('youtube_json_trackers', ['mes' => 'septiembre', 'anio' => 2026]);
        $this->assertDatabaseHas('video_social_publications', ['platform' => 'facebook', 'publicado' => 'si', 'link' => 'https://www.facebook.com/video']);
        $this->assertDatabaseHas('video_social_publications', ['platform' => 'tiktok', 'publicado' => 'no']);
        $video = YoutubeJsonTracker::where('titulo', 'Diagnostico de lavadora')->firstOrFail();
        $this->assertSame(
            ['https://www.youtube.com/playlist?list=abc123'],
            $video->playlists()->pluck('url')->all(),
        );
        $facebook = $video->socialPublications()->where('platform', 'facebook')->firstOrFail();
        $this->assertSame('septiembre', $facebook->mes);
        $this->assertSame(2026, $facebook->anio);
        Storage::disk('public')->assertExists($video->youtube_miniatura);
        Storage::disk('public')->assertExists($facebook->miniatura);
    }

    public function test_existing_video_can_add_a_playlist_without_creating_a_duplicate_video(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $video = YoutubeJsonTracker::factory()->for($user)->create([
            'titulo' => 'Video original',
            'titulo_lista' => 'Lista original',
            'link_lista' => 'https://www.youtube.com/playlist?list=original',
        ]);
        $originalPlaylist = $user->playlists()->create([
            'name' => 'Lista original',
            'url' => 'https://www.youtube.com/playlist?list=original',
        ]);
        $video->playlists()->attach($originalPlaylist);
        $newPlaylist = [
            'playlist_title' => 'Lista nueva',
            'playlist_url' => 'https://www.youtube.com/playlist?list=nueva',
        ];

        $this->actingAs($user)->get(route('videos.edit', $video))
            ->assertOk()
            ->assertSee('Lista original')
            ->assertSee('Agregar solo esta lista');

        $this->actingAs($user)
            ->post(route('videos.playlists.store', $video), $newPlaylist)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('videos.edit', $video));

        $this->actingAs($user)
            ->post(route('videos.playlists.store', $video), $newPlaylist)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('youtube_json_trackers', 1);
        $this->assertDatabaseCount('playlists', 2);
        $this->assertDatabaseCount('playlist_video', 2);
        $this->assertDatabaseHas('youtube_json_trackers', [
            'id' => $video->id,
            'titulo' => 'Video original',
            'link_lista' => 'https://www.youtube.com/playlist?list=original',
        ]);
        $this->assertSame(
            ['https://www.youtube.com/playlist?list=original', 'https://www.youtube.com/playlist?list=nueva'],
            $video->playlists()->orderBy('playlists.id')->pluck('url')->all(),
        );
    }

    public function test_missing_upload_date_defaults_to_five_days_before_publication_and_keeps_manual_date(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $automaticData = array_merge($this->videoData(), [
            'fecha_publicacion' => '2024-08-05',
            'youtube_link' => 'https://www.youtube.com/watch?v=autodate1234',
        ]);
        unset($automaticData['fecha_subido']);

        $this->actingAs($user)->post(route('videos.store'), $automaticData)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('videos.index'));

        $automaticVideo = YoutubeJsonTracker::where('youtube_link', $automaticData['youtube_link'])->firstOrFail();
        $this->assertSame('2024-07-31', $automaticVideo->fecha_subido->format('Y-m-d'));

        $manualData = array_merge($this->videoData(), [
            'fecha_subido' => '2024-08-01',
            'fecha_publicacion' => '2024-08-05',
            'youtube_link' => 'https://www.youtube.com/watch?v=manualdate1',
        ]);

        $this->actingAs($user)->post(route('videos.store'), $manualData)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('videos.index'));

        $manualVideo = YoutubeJsonTracker::where('youtube_link', $manualData['youtube_link'])->firstOrFail();
        $this->assertSame('2024-08-01', $manualVideo->fecha_subido->format('Y-m-d'));
    }

    public function test_video_form_rejects_invalid_required_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('videos.store'), ['titulo' => 'Sin datos']);

        $response->assertSessionHasErrors(['tipo_aparato', 'descripcion']);
        $this->assertDatabaseCount('youtube_json_trackers', 0);
    }

    public function test_video_form_keeps_related_suggestions_out_of_the_youtube_field(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);

        $this->actingAs($user)->get(route('videos.create'))
            ->assertOk()
            ->assertDontSee('Hasta 10 videos relacionados por modelo, tema o capacidad')
            ->assertSee('Subido como privado')
            ->assertSee('id="youtube-link-check"', false);
    }

    public function test_new_video_form_can_reuse_the_users_most_recent_playlist(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $otherUser = User::factory()->create();
        YoutubeJsonTracker::factory()->for($user)->create([
            'titulo_lista' => 'Lista anterior',
            'link_lista' => 'https://www.youtube.com/playlist?list=older',
        ]);
        YoutubeJsonTracker::factory()->for($user)->create([
            'titulo_lista' => 'Lista Xpert System 17 kg',
            'link_lista' => 'https://www.youtube.com/playlist?list=xpert-system',
        ]);
        YoutubeJsonTracker::factory()->for($otherUser)->create([
            'titulo_lista' => 'Lista de otra persona',
            'link_lista' => 'https://www.youtube.com/playlist?list=private',
        ]);

        $this->actingAs($user)->get(route('videos.create'))
            ->assertOk()
            ->assertSee('Usar la misma lista del último video registrado: <strong>Lista Xpert System 17 kg</strong>', false)
            ->assertSee('data-titulo-lista="Lista Xpert System 17 kg"', false)
            ->assertSee('data-link-lista="https://www.youtube.com/playlist?list=xpert-system"', false)
            ->assertDontSee('Lista de otra persona');
    }

    public function test_video_list_selection_uses_the_selected_videos_model_topic_and_capacity(): void
    {
        $user = User::factory()->create();
        YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'WW16',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Samsung',
            'kg' => 16,
            'tema_error' => 'Perillas flojas',
        ]);

        $this->actingAs($user)->get(route('videos.index'))
            ->assertOk()
            ->assertSee('data-modelo="WW16"', false)
            ->assertSee('data-tema-error="Perillas flojas"', false)
            ->assertSee('data-kg="16.00"', false)
            ->assertSee('id="related-videos-results"', false)
            ->assertSee('Copiar toda la tarjeta')
            ->assertSee('Enlaces relacionados');
    }

    public function test_video_can_be_created_without_youtube_link_and_social_defaults_to_no(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $data = $this->videoData();
        $data['youtube_link'] = '';
        unset($data['social']);

        $this->actingAs($user)->post(route('videos.store'), $data)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('videos.index'));

        $video = YoutubeJsonTracker::where('titulo', $data['titulo'])->firstOrFail();
        $this->assertNull($video->youtube_link);
        $this->assertDatabaseHas('video_social_publications', [
            'youtube_json_tracker_id' => $video->id,
            'platform' => 'facebook',
            'publicado' => 'no',
        ]);
    }

    public function test_video_search_matches_youtube_playlist_and_social_links(): void
    {
        $user = User::factory()->create();
        $video = YoutubeJsonTracker::factory()->for($user)->create([
            'youtube_link' => 'https://www.youtube.com/watch?v=unique-youtube-check-123',
            'link_lista' => 'https://www.youtube.com/playlist?list=unique-playlist-check-456',
        ]);
        $video->socialPublications()->create([
            'platform' => 'facebook',
            'publicado' => 'si',
            'link' => 'https://www.facebook.com/unique-social-check-789',
        ]);
        YoutubeJsonTracker::factory()->for($user)->create();
        YoutubeJsonTracker::factory()->create([
            'youtube_link' => 'https://www.youtube.com/watch?v=unique-youtube-check-123',
        ]);

        foreach (['unique-youtube-check-123', 'unique-playlist-check-456', 'unique-social-check-789'] as $term) {
            $this->actingAs($user)->get(route('videos.index', ['q' => $term]))
                ->assertOk()
                ->assertViewHas('videos', fn ($videos) => $videos->total() === 1 && $videos->first()->is($video));
        }
    }

    public function test_video_search_matches_a_short_youtube_link_to_the_registered_video(): void
    {
        $user = User::factory()->create();
        $video = YoutubeJsonTracker::factory()->for($user)->create([
            'youtube_link' => 'https://www.youtube.com/watch?v=SLpceHgf53Q',
        ]);

        $this->actingAs($user)->get(route('videos.index', ['q' => 'https://youtu.be/SLpceHgf53Q']))
            ->assertOk()
            ->assertViewHas('videos', fn ($videos) => $videos->total() === 1 && $videos->first()->is($video));
    }

    public function test_youtube_link_check_detects_short_and_long_url_duplicates_for_the_user(): void
    {
        $user = User::factory()->create();
        $existingVideo = YoutubeJsonTracker::factory()->for($user)->create([
            'titulo' => 'Video existente',
            'youtube_link' => 'https://www.youtube.com/watch?v=qWqvYfCvHNQ',
        ]);
        YoutubeJsonTracker::factory()->create([
            'youtube_link' => 'https://www.youtube.com/watch?v=other-user-video',
        ]);

        $this->actingAs($user)->getJson(route('videos.check-youtube-link', [
            'url' => 'https://youtu.be/qWqvYfCvHNQ',
        ]))
            ->assertOk()
            ->assertJsonPath('exists', true)
            ->assertJsonPath('id', $existingVideo->id)
            ->assertJsonPath('titulo', 'Video existente')
            ->assertJsonPath('edit_url', route('videos.edit', $existingVideo));
    }

    public function test_youtube_link_check_ignores_the_video_being_edited(): void
    {
        $user = User::factory()->create();
        $video = YoutubeJsonTracker::factory()->for($user)->create([
            'youtube_link' => 'https://www.youtube.com/watch?v=qWqvYfCvHNQ',
        ]);

        $this->actingAs($user)->getJson(route('videos.check-youtube-link', [
            'url' => 'https://youtu.be/qWqvYfCvHNQ',
            'video_id' => $video->id,
        ]))
            ->assertOk()
            ->assertJsonPath('exists', false);
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
        ])->assertSessionHasNoErrors()->assertRedirect(route('videos.index'));

        $this->assertDatabaseHas('youtube_json_trackers', [
            'user_id' => $user->id,
            'youtube_publicado' => 'no',
            'titulo' => null,
            'youtube_link' => null,
        ]);
    }

    public function test_private_youtube_video_keeps_optional_details_without_publication_date_or_related_links(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $this->actingAs($user)->post(route('videos.store'), array_merge($this->videoData(), [
            'youtube_publicado' => 'privado',
            'fecha_subido' => '2026-09-05',
            'fecha_publicacion' => '',
            'titulo' => 'Borrador privado',
            'formato' => '',
            'youtube_link' => 'https://www.youtube.com/watch?v=abcdef12345',
            'link_lista' => 'https://www.youtube.com/playlist?list=private-list',
            'social' => [
                'facebook' => [
                    'publicado' => 'si',
                    'fecha' => '2026-09-06',
                    'link' => 'https://www.facebook.com/private-video',
                ],
            ],
        ]))->assertSessionHasNoErrors()->assertRedirect(route('videos.index'));

        $privateVideo = YoutubeJsonTracker::where('youtube_publicado', 'privado')->firstOrFail();
        $this->assertSame('2026-09-05', $privateVideo->fecha_subido->format('Y-m-d'));
        $this->assertNull($privateVideo->fecha_publicacion);
        $this->assertNull($privateVideo->mes);
        $this->assertNull($privateVideo->anio);
        $this->assertNull($privateVideo->red_social);
        $this->assertSame('Borrador privado', $privateVideo->titulo);
        $this->assertSame('https://www.youtube.com/watch?v=abcdef12345', $privateVideo->youtube_link);

        $reference = YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => $privateVideo->modelo,
            'tipo_aparato' => $privateVideo->tipo_aparato,
            'marca' => $privateVideo->marca,
            'kg' => $privateVideo->kg,
        ]);
        $privateYoutubeOnlyVideo = YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => $privateVideo->modelo,
            'tipo_aparato' => $privateVideo->tipo_aparato,
            'marca' => $privateVideo->marca,
            'kg' => $privateVideo->kg,
            'youtube_publicado' => 'privado',
            'youtube_link' => 'https://www.youtube.com/watch?v=bcdef123456',
        ]);

        $videos = collect($this->actingAs($user)->getJson(route('videos.related-search', [
            'video_id' => $reference->id,
            'modelo' => $privateVideo->modelo,
            'tipo_aparato' => $privateVideo->tipo_aparato,
            'marca' => $privateVideo->marca,
            'kg' => $privateVideo->kg,
        ]))->assertOk()->json())->flatMap(fn ($group) => $group['videos']);

        $this->assertFalse($videos->contains('id', $privateVideo->id));
        $this->assertFalse($videos->contains('id', $privateYoutubeOnlyVideo->id));

        $this->actingAs($user)->get(route('videos.index', ['q' => 'abcdef12345']))
            ->assertOk()
            ->assertViewHas('videos', fn ($results) => $results->contains('id', $privateVideo->id));

        $this->actingAs($user)->get(route('videos.index'))
            ->assertOk()
            ->assertDontSee('https://www.youtube.com/watch?v=abcdef12345')
            ->assertDontSee('https://www.youtube.com/playlist?list=private-list')
            ->assertDontSee('https://www.youtube.com/watch?v=bcdef123456');

        $privateWithoutMetadata = array_merge($this->videoData(), [
            'youtube_publicado' => 'privado',
            'fecha_subido' => '2026-09-08',
        ]);
        unset($privateWithoutMetadata['fecha_publicacion'], $privateWithoutMetadata['titulo'], $privateWithoutMetadata['youtube_link'], $privateWithoutMetadata['formato']);

        $this->actingAs($user)->post(route('videos.store'), $privateWithoutMetadata)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('videos.index'));
        $this->assertDatabaseHas('youtube_json_trackers', [
            'user_id' => $user->id,
            'youtube_publicado' => 'privado',
            'fecha_subido' => '2026-09-08',
            'fecha_publicacion' => null,
            'titulo' => null,
        ]);

        $this->actingAs($user)->put(route('videos.update', $privateVideo), array_merge($this->videoData(), [
            'youtube_publicado' => 'si',
            'fecha_publicacion' => '2026-09-07',
            'titulo' => 'Video publicado',
            'youtube_link' => 'https://www.youtube.com/watch?v=abcdef12345',
            'social' => [],
        ]))->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('youtube_json_trackers', [
            'id' => $privateVideo->id,
            'youtube_publicado' => 'si',
            'fecha_subido' => '2026-08-26',
            'fecha_publicacion' => '2026-09-07',
            'titulo' => 'Video publicado',
            'youtube_link' => 'https://www.youtube.com/watch?v=abcdef12345',
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
            ->assertJsonCount(2)
            ->assertJsonPath('0.modelo', 'RT32')
            ->assertJsonCount(2, '0.videos')
            ->assertJsonPath('1.modelo', 'RT35');
    }

    public function test_related_video_selection_limits_to_ten_and_prioritizes_model_then_capacity(): void
    {
        $user = User::factory()->create();
        $selectedVideo = YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'WW16',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Samsung',
            'kg' => 16,
        ]);
        $exactModel = YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'WW16',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Samsung',
            'kg' => 16,
        ]);
        $sameCapacity = YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'WW20',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'LG',
            'kg' => 16,
        ]);
        YoutubeJsonTracker::factory()->count(8)->for($user)->create([
            'modelo' => 'WW'.fake()->unique()->numberBetween(30, 99),
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Samsung',
            'kg' => 18,
        ]);

        $response = $this->actingAs($user)->getJson(route('videos.related-search', [
            'video_id' => $selectedVideo->id,
            'modelo' => 'WW16',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Samsung',
            'kg' => 16,
        ]));

        $response->assertOk();
        $suggestedIds = collect($response->json())->flatMap(fn ($group) => $group['videos'])->pluck('id');
        $this->assertCount(10, $suggestedIds);
        $this->assertSame($exactModel->id, $suggestedIds->first());
        $this->assertSame($sameCapacity->id, $suggestedIds->get(1));
    }

    public function test_dashboard_related_references_can_include_all_twelve_videos_for_a_model(): void
    {
        $user = User::factory()->create();
        $reference = YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'Xpert System',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Whirlpool',
            'kg' => 17,
        ]);
        $relatedVideos = YoutubeJsonTracker::factory()->count(11)->for($user)->create([
            'modelo' => 'Xpert System',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Whirlpool',
            'kg' => 17,
        ]);
        $playlistOnlyVideo = $relatedVideos->first();
        $playlistOnlyVideo->update([
            'youtube_link' => null,
            'link_lista' => 'https://www.youtube.com/playlist?list=xpert-system',
            'titulo_lista' => 'Whirlpool Xpert System 17 kg',
        ]);

        $response = $this->actingAs($user)->getJson(route('videos.related-search', [
            'video_id' => $reference->id,
            'modelo' => 'Xpert System',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Whirlpool',
            'kg' => 17,
            'limit' => 20,
            'incluir_referencia' => 1,
        ]));

        $response->assertOk();
        $suggestedVideos = collect($response->json())->flatMap(fn ($group) => $group['videos']);
        $this->assertCount(12, $suggestedVideos);
        $this->assertTrue($suggestedVideos->contains('id', $reference->id));
        $this->assertTrue($suggestedVideos->contains(fn ($video) => $video['link_lista'] === 'https://www.youtube.com/playlist?list=xpert-system'));
    }

    public function test_related_video_selection_matches_topic_keywords_such_as_perillas(): void
    {
        $user = User::factory()->create();
        $matchingVideo = YoutubeJsonTracker::factory()->for($user)->create([
            'titulo' => 'Reparacion de perillas',
            'tema_error' => 'Perillas flojas',
        ]);
        YoutubeJsonTracker::factory()->for($user)->create([
            'titulo' => 'Otro tema',
            'tema_error' => 'Otro problema',
        ]);

        $response = $this->actingAs($user)->getJson(route('videos.related-search', [
            'tema_error' => 'perillas',
        ]))
            ->assertOk();

        $suggestedIds = collect($response->json())->flatMap(fn ($group) => $group['videos'])->pluck('id');
        $this->assertSame([$matchingVideo->id], $suggestedIds->all());
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
            ->assertJsonCount(2)
            ->assertJsonPath('0.modelo', 'RT35')
            ->assertJsonPath('1.modelo', 'WA90');
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
            'youtube_link' => 'https://youtube.com/watch?v=rt38',
            'link_lista' => 'https://youtube.com/playlist?list=rt38',
            'created_at' => now()->subMinutes(2),
        ]);
        YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'WA90',
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Samsung',
            'titulo' => 'Lavadora Samsung relacionada',
            'youtube_link' => 'https://youtube.com/watch?v=wa90',
            'link_lista' => 'https://youtube.com/playlist?list=wa90',
            'created_at' => now()->subMinute(),
        ]);
        $latestRefrigerator = YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'RT38',
            'tipo_aparato' => 'Nevera',
            'marca' => 'Samsung',
            'titulo' => 'Nevera Samsung principal',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('videos.index'));

        $response
            ->assertOk()
            ->assertViewHas('suggestions', function ($suggestions) use ($previousRefrigerator, $latestRefrigerator) {
                return $suggestions->pluck('modelo')->contains('RT38')
                    && $suggestions->pluck('modelo')->contains('WA90')
                    && $suggestions->contains('id', $previousRefrigerator->id)
                    && ! $suggestions->contains('id', $latestRefrigerator->id);
            });

        preg_match_all('/data-copy="([^"]*)"/', $response->getContent(), $copyCards);
        $youtubeCards = array_values(array_filter($copyCards[1], fn ($copyText) => str_starts_with($copyText, 'Modelo: ')));
        $this->assertCount(2, $youtubeCards);
        $this->assertStringContainsString('https://youtube.com/watch?v=rt38', $youtubeCards[0]);
        $this->assertStringNotContainsString('https://youtube.com/watch?v=wa90', $youtubeCards[0]);
        $this->assertStringContainsString('https://youtube.com/playlist?list=rt38', $youtubeCards[0]);
        $this->assertStringNotContainsString('https://youtube.com/playlist?list=wa90', $youtubeCards[0]);
        $this->assertStringContainsString('https://youtube.com/watch?v=wa90', $youtubeCards[1]);
        $this->assertStringNotContainsString('https://youtube.com/watch?v=rt38', $youtubeCards[1]);
        $this->assertStringContainsString('https://youtube.com/playlist?list=wa90', $youtubeCards[1]);
        $this->assertStringNotContainsString('https://youtube.com/playlist?list=rt38', $youtubeCards[1]);
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