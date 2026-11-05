<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\YoutubeJsonTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_search_matches_video_model_and_topic_for_the_current_user(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $video = YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'WW16',
            'tema_error' => 'Perillas flojas',
        ]);
        YoutubeJsonTracker::factory()->for($user)->create([
            'modelo' => 'RT32',
            'tema_error' => 'Fuga de agua',
        ]);
        YoutubeJsonTracker::factory()->create([
            'modelo' => 'WW16',
            'tema_error' => 'Perillas flojas',
        ]);

        foreach (['WW16', 'Perillas flojas'] as $term) {
            $this->actingAs($user)->get(route('dashboard', ['q' => $term]))
                ->assertOk()
                ->assertViewHas('searchResults', fn ($results) => $results->count() === 1 && $results->first()->is($video))
                ->assertViewHas('total', 2);
        }
    }

    public function test_dashboard_search_displays_no_match_message(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);

        $this->actingAs($user)->get(route('dashboard', ['q' => 'modelo-inexistente']))
            ->assertOk()
            ->assertSee('No se encontraron videos con esa palabra o frase.');
    }

    public function test_dashboard_search_finds_all_words_across_video_fields_without_a_model(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $matchingVideo = YoutubeJsonTracker::factory()->for($user)->create([
            'tipo_aparato' => 'Refrigeradora',
            'marca' => 'Samsung',
            'modelo' => null,
            'tema_error' => 'Reinicio de software',
            'titulo' => '¡Reinicia el Software Tu Refrigeradora Samsung Twin Cooling Plus en Minutos!',
        ]);

        $this->actingAs($user)->get(route('dashboard', ['q' => 'refrigeradora samsung reinicio']))
            ->assertOk()
            ->assertViewHas('searchResults', fn ($results) => $results->count() === 1 && $results->first()->is($matchingVideo))
            ->assertSee('¡Reinicia el Software Tu Refrigeradora Samsung Twin Cooling Plus en Minutos!')
            ->assertSee('data-video-id="'.$matchingVideo->id.'"', false)
            ->assertSee('data-modelo=""', false)
            ->assertSee('class="related-video-button', false)
            ->assertSee('id="related-videos-panel"', false)
            ->assertSee('Copiar toda la tarjeta')
            ->assertSee('Enlaces relacionados')
            ->assertSee('data-auto-select="true"', false)
            ->assertSee('data-related-limit="20"', false)
            ->assertSee(route('videos.related-search'), false);
    }

    public function test_dashboard_search_matches_appliance_brand_capacity_and_commercial_model_phrase(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $matchingVideo = YoutubeJsonTracker::factory()->for($user)->create([
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Whirlpool',
            'kg' => 17,
            'modelo_comercial' => 'Xpert System',
            'modelo' => '8MWTW',
            'tema_error' => 'Reiniciar tarjeta',
            'titulo' => 'Diagnóstico del sistema de lavado',
        ]);
        YoutubeJsonTracker::factory()->for($user)->create([
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Whirlpool',
            'kg' => 15,
            'modelo_comercial' => 'Xpert System',
            'tema_error' => 'Reiniciar tarjeta',
        ]);

        $this->actingAs($user)->get(route('dashboard', ['q' => 'reinicio lavadora whirlpool de 17 kg xpert system']))
            ->assertOk()
            ->assertViewHas('searchResults', fn ($results) => $results->count() === 1 && $results->first()->is($matchingVideo));
    }

    public function test_dashboard_search_prioritizes_appliance_and_brand_when_model_is_not_known(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $otherBrand = YoutubeJsonTracker::factory()->for($user)->create([
            'tipo_aparato' => 'Lavadora',
            'marca' => 'LG',
            'kg' => 17,
            'titulo' => 'Reinicio y calibración completa de lavadora',
        ]);
        $matchingVideo = YoutubeJsonTracker::factory()->for($user)->create([
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Whirlpool',
            'kg' => 17,
            'titulo' => 'Reiniciar lavadora Xpert System',
        ]);
        YoutubeJsonTracker::factory()->for($user)->create([
            'tipo_aparato' => 'Nevera frio seco',
            'marca' => 'Whirlpool',
            'kg' => 17,
            'titulo' => 'Reinicio Whirlpool',
        ]);

        $this->actingAs($user)->get(route('dashboard', ['q' => 'reinicio lavadora whirlpool 17 kg']))
            ->assertOk()
            ->assertViewHas('searchResults', fn ($results) => $results->first()->is($matchingVideo)
                && $results->contains(fn ($video) => $video->is($otherBrand)));
    }

    public function test_refrigeradora_search_recognizes_nevera_appliance_type(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $matchingVideo = YoutubeJsonTracker::factory()->for($user)->create([
            'tipo_aparato' => 'Nevera frio seco',
            'marca' => 'Whirlpool',
            'titulo' => 'Reparar refrigeradora Whirlpool',
        ]);
        YoutubeJsonTracker::factory()->for($user)->create([
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Whirlpool',
            'titulo' => 'Reparar refrigeradora Whirlpool',
        ]);

        $this->actingAs($user)->get(route('dashboard', ['q' => 'refrigeradora Whirlpool']))
            ->assertOk()
            ->assertViewHas('searchResults', fn ($results) => $results->first()->is($matchingVideo));
    }

    public function test_dashboard_automatically_uses_the_latest_video_for_related_references(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $reference = YoutubeJsonTracker::factory()->for($user)->create([
            'tipo_aparato' => 'Lavadora',
            'marca' => 'Whirlpool',
            'modelo' => 'Xpert System',
            'kg' => 17,
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-video-id="'.$reference->id.'"', false)
            ->assertSee('data-modelo="Xpert System"', false)
            ->assertSee('data-marca="Whirlpool"', false)
            ->assertSee('data-kg="17.00"', false)
            ->assertSee('data-include-reference="true"', false);
    }

    public function test_dashboard_playlist_selector_shows_every_video_in_the_selected_playlist(): void
    {
        $user = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $anotherUser = User::factory()->create(['categoria_principal' => 'Linea blanca']);
        $playlist = $user->playlists()->create([
            'name' => 'Lavadoras Samsung',
            'url' => 'https://www.youtube.com/playlist?list=samsung',
        ]);
        $firstVideo = YoutubeJsonTracker::factory()->for($user)->create([
            'titulo' => 'Primer video Samsung',
            'youtube_link' => 'https://www.youtube.com/watch?v=primerVideo1',
            'fecha_publicacion' => '2026-01-01',
        ]);
        $secondVideo = YoutubeJsonTracker::factory()->for($user)->create([
            'titulo' => 'Segundo video Samsung',
            'youtube_link' => 'https://www.youtube.com/watch?v=segundoVideo2',
            'fecha_publicacion' => '2026-02-01',
        ]);
        $unrelatedVideo = YoutubeJsonTracker::factory()->for($user)->create(['titulo' => 'Video sin lista']);
        $otherUsersVideo = YoutubeJsonTracker::factory()->for($anotherUser)->create(['titulo' => 'Video de otra persona']);
        $playlist->videos()->attach([$firstVideo->id, $secondVideo->id, $otherUsersVideo->id]);

        $this->actingAs($user)->get(route('dashboard', ['playlist_id' => $playlist->id]))
            ->assertOk()
            ->assertViewHas('selectedPlaylist', fn ($selected) => $selected->is($playlist))
            ->assertViewHas('playlistVideos', fn ($videos) => $videos->count() === 2
                && $videos->contains(fn ($video) => $video->is($firstVideo))
                && $videos->contains(fn ($video) => $video->is($secondVideo)))
            ->assertViewHas('playlistCopyText', "Lista: Lavadoras Samsung\nhttps://www.youtube.com/playlist?list=samsung\n\nVideos:\n\n- Segundo video Samsung\nhttps://www.youtube.com/watch?v=segundoVideo2\n\n- Primer video Samsung\nhttps://www.youtube.com/watch?v=primerVideo1")
            ->assertSee('Lavadoras Samsung')
            ->assertSee('Copiar contenido para descripción')
            ->assertSee('Primer video Samsung')
            ->assertSee('Segundo video Samsung')
            ->assertSee('data-playlist-video="'.$firstVideo->id.'"', false)
            ->assertSee('data-playlist-video="'.$secondVideo->id.'"', false)
            ->assertDontSee('data-playlist-video="'.$unrelatedVideo->id.'"', false)
            ->assertDontSee('data-playlist-video="'.$otherUsersVideo->id.'"', false);
    }
}
