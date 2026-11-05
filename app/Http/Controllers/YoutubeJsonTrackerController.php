<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoStoreRequest;
use App\Models\YoutubeJsonTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class YoutubeJsonTrackerController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->user()->videos()->with('socialPublications')->latest();

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $youtubeVideoId = $this->extractYoutubeVideoId($term);
            $query->where(function ($videoQuery) use ($term, $youtubeVideoId) {
                $videoQuery->where('titulo', 'like', "%{$term}%")
                    ->orWhere('modelo', 'like', "%{$term}%")
                    ->orWhere('marca', 'like', "%{$term}%")
                    ->orWhere('tema_error', 'like', "%{$term}%")
                    ->orWhere('youtube_link', 'like', "%{$term}%")
                    ->orWhere('link_lista', 'like', "%{$term}%")
                    ->orWhereHas('socialPublications', fn ($socialQuery) => $socialQuery->where('link', 'like', "%{$term}%"));

                if ($youtubeVideoId !== null) {
                    $videoQuery->orWhere('youtube_link', 'like', "%{$youtubeVideoId}%");
                }
            });
        }

        if ($request->filled('tipo_aparato')) {
            $query->where('tipo_aparato', $request->string('tipo_aparato')->toString());
        }

        if ($request->filled('red_social')) {
            $platform = $request->string('red_social')->toString();
            if ($platform === 'youtube') {
                $query->where('red_social', 'YouTube');
            } else {
                $query->whereHas('socialPublications', fn ($socialQuery) => $socialQuery->where('platform', $platform)->where('publicado', 'si'));
            }
        }

        if ($request->filled('formato')) {
            $format = $request->string('formato')->toString();
            $query->where(function ($formatQuery) use ($format) {
                $formatQuery->where('formato', $format)
                    ->orWhereHas('socialPublications', fn ($socialQuery) => $socialQuery->where('tipo', $format)->where('publicado', 'si'));
            });
        }

        $videos = $query->paginate(10)->withQueryString();
        $total = $request->user()->videos()->count();
        $latest = $request->user()->videos()->latest()->first();
        $types = $request->user()->videos()->select('tipo_aparato')->distinct()->orderBy('tipo_aparato')->pluck('tipo_aparato');
        $suggestionContext = $request->string('q')->trim()->toString();
        if ($suggestionContext === '' && $latest) {
            $suggestionContext = $latest->tipo_aparato.' / '.$latest->modelo.' / '.$latest->kg.' KG';
        }

        $suggestions = collect();
        if ($suggestionContext !== '') {
            $suggestionsQuery = $request->user()->videos()
                ->where('youtube_publicado', 'si')
                ->with('socialPublications');

            if ($request->filled('q')) {
                $suggestions = $suggestionsQuery
                    ->where(function ($suggestionQuery) use ($suggestionContext) {
                        $suggestionQuery->where('titulo', 'like', "%{$suggestionContext}%")
                            ->orWhere('modelo', 'like', "%{$suggestionContext}%")
                            ->orWhere('marca', 'like', "%{$suggestionContext}%")
                            ->orWhere('tipo_aparato', 'like', "%{$suggestionContext}%");
                    })
                    ->latest()
                    ->get()
                    ->unique('id')
                    ->groupBy(fn (YoutubeJsonTracker $video) => trim((string) $video->modelo) ?: 'Sin modelo')
                    ->map(fn ($modelVideos) => $modelVideos->take(8))
                    ->flatten(1)
                    ->values();
            } else {
                $mainModelVideos = (clone $suggestionsQuery)
                    ->where('modelo', $latest->modelo)
                    ->where('id', '!=', $latest->id)
                    ->latest()
                    ->get();
                $fallbackVideos = collect();
                $refrigeratorTypes = ['Nevera', 'Nevera frio seco', 'Frigobar', 'Camara', 'Nevera pequeña'];
                $fallbackTypes = in_array($latest->tipo_aparato, $refrigeratorTypes, true)
                    ? [...$refrigeratorTypes, 'Lavadora']
                    : $refrigeratorTypes;

                if ($mainModelVideos->count() < 8) {
                    $fallbackVideos = (clone $suggestionsQuery)
                        ->where('id', '!=', $latest->id)
                        ->where('marca', $latest->marca)
                        ->whereIn('tipo_aparato', $fallbackTypes)
                        ->latest()
                        ->get();
                }

                if ($mainModelVideos->count() < 8 && $fallbackVideos->isEmpty()) {
                    $washingMachineModel = (clone $suggestionsQuery)
                        ->where('id', '!=', $latest->id)
                        ->where('tipo_aparato', 'Lavadora')
                        ->where('modelo', '!=', $latest->modelo)
                        ->inRandomOrder()
                        ->value('modelo');

                    if ($washingMachineModel) {
                        $fallbackVideos = (clone $suggestionsQuery)->where('id', '!=', $latest->id)->where('modelo', $washingMachineModel)->latest()->get();
                    }
                }

                $suggestions = $mainModelVideos
                    ->concat($fallbackVideos)
                    ->unique('id')
                    ->groupBy(fn (YoutubeJsonTracker $video) => trim((string) $video->modelo) ?: 'Sin modelo')
                    ->map(fn ($modelVideos) => $modelVideos->take(8))
                    ->sortBy(fn ($modelVideos, string $model) => $model === $latest->modelo ? 0 : 1)
                    ->values()
                    ->flatten(1)
                    ->values();
            }
        }

        return view('videos.index', compact('videos', 'total', 'latest', 'types', 'suggestions', 'suggestionContext'));
    }

    public function create(Request $request): View
    {
        $ultimaLista = $request->user()->playlists()->latest('id')->first();
        $ultimaListaTitulo = $ultimaLista?->name;
        $ultimaListaLink = $ultimaLista?->url;

        if (! $ultimaLista) {
            $ultimaLista = $request->user()->videos()
            ->whereNotNull('link_lista')
            ->where('link_lista', '!=', '')
            ->where('youtube_publicado', 'si')
            ->latest('id')
            ->first(['titulo_lista', 'link_lista']);
            $ultimaListaTitulo = $ultimaLista?->titulo_lista;
            $ultimaListaLink = $ultimaLista?->link_lista;
        }

        return view('videos.create', [
            'video' => new YoutubeJsonTracker,
            'ultimaListaTitulo' => $ultimaListaTitulo,
            'ultimaListaLink' => $ultimaListaLink,
        ]);
    }

    public function relatedSearch(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'incluir_referencia' => ['sometimes', 'boolean'],
        ]);
        $model = trim((string) $request->query('modelo'));
        $type = trim((string) $request->query('tipo_aparato'));
        $brand = trim((string) $request->query('marca'));
        $kg = $request->query('kg');
        $keyword = trim((string) $request->query('tema_error'));
        $currentId = $request->integer('video_id');
        $limit = $request->integer('limit', 10);
        $includeReference = $request->boolean('incluir_referencia');
        $refrigeratorTypes = ['Nevera', 'Nevera frio seco', 'Frigobar', 'Camara', 'Nevera pequeña'];
        $fallbackTypes = in_array($type, $refrigeratorTypes, true)
            ? ['Lavadora']
            : $refrigeratorTypes;

        if ($model === '' && $type === '' && $kg === null && $keyword === '') {
            return response()->json([]);
        }

        $videos = $request->user()->videos()
            ->with(['socialPublications' => fn ($query) => $query->whereIn('platform', ['facebook', 'tiktok'])->where('publicado', 'si')->whereNotNull('link')])
            ->select(['id', 'tipo_aparato', 'marca', 'kg', 'modelo', 'titulo', 'tema_error', 'descripcion', 'youtube_publicado', 'youtube_link', 'titulo_lista', 'link_lista'])
            ->where(function ($query) {
                $query->where(function ($youtubeQuery) {
                    $youtubeQuery->where('youtube_publicado', 'si')
                        ->where(fn ($linkQuery) => $linkQuery->whereNotNull('youtube_link')->orWhereNotNull('link_lista'));
                })
                    ->orWhereHas('socialPublications', fn ($socialQuery) => $socialQuery->whereIn('platform', ['facebook', 'tiktok'])->where('publicado', 'si')->whereNotNull('link'));
            })
            ->where('youtube_publicado', '!=', 'privado')
            ->where(function ($query) use ($model, $type, $brand, $kg, $keyword, $fallbackTypes) {
                if ($model !== '') {
                    $query->orWhere('modelo', 'like', "%{$model}%");
                }
                if ($keyword !== '') {
                    $query->orWhere('titulo', 'like', "%{$keyword}%")
                        ->orWhere('tema_error', 'like', "%{$keyword}%")
                        ->orWhere('descripcion', 'like', "%{$keyword}%");
                }
                if ($type !== '') {
                    $query->orWhere(function ($relatedQuery) use ($type, $brand, $kg) {
                        $relatedQuery->where('tipo_aparato', $type);
                        if ($kg !== null && $brand !== '') {
                            $relatedQuery->where(fn ($matchQuery) => $matchQuery->where('kg', $kg)->orWhere('marca', $brand));
                        } elseif ($kg !== null) {
                            $relatedQuery->where('kg', $kg);
                        } elseif ($brand !== '') {
                            $relatedQuery->where('marca', $brand);
                        }
                    });
                    if ($brand !== '') {
                        $query->orWhere(fn ($relatedQuery) => $relatedQuery->where('marca', $brand)->whereIn('tipo_aparato', $fallbackTypes));
                    }
                } elseif ($kg !== null) {
                    $query->orWhere('kg', $kg);
                }
            })
            ->when($currentId > 0 && ! $includeReference, fn ($query) => $query->where('id', '!=', $currentId));
        $videos = $videos->get()
            ->sortBy(function (YoutubeJsonTracker $video) use ($model, $type, $brand, $kg, $keyword, $fallbackTypes) {
                if ($model !== '' && strcasecmp(trim((string) $video->modelo), $model) === 0) {
                    return 0;
                }
                if ($model !== '' && stripos((string) $video->modelo, $model) !== false) {
                    return 1;
                }
                if ($keyword !== '' && (
                    stripos((string) $video->titulo, $keyword) !== false
                    || stripos((string) $video->tema_error, $keyword) !== false
                    || stripos((string) $video->descripcion, $keyword) !== false
                )) {
                    return 2;
                }
                if ($type !== '' && $video->tipo_aparato === $type && $kg !== null && (float) $video->kg === (float) $kg) {
                    return 3;
                }
                if ($type !== '' && $video->tipo_aparato === $type && $brand !== '' && $video->marca === $brand) {
                    return 4;
                }
                if ($kg !== null && (float) $video->kg === (float) $kg) {
                    return 5;
                }
                if ($brand !== '' && in_array($video->tipo_aparato, $fallbackTypes, true) && $video->marca === $brand) {
                    return 6;
                }

                return 7;
            })
            ->take($limit)
            ->values()
            ->groupBy(fn (YoutubeJsonTracker $video) => trim((string) $video->modelo) ?: 'Sin modelo')
            ->map(fn ($modelVideos, string $model) => [
                'modelo' => $model,
                'videos' => $modelVideos->map(fn (YoutubeJsonTracker $video) => [
                    'id' => $video->id,
                    'titulo' => $video->titulo,
                    'link' => $video->youtube_publicado === 'si' ? $video->youtube_link : null,
                    'titulo_lista' => $video->titulo_lista,
                    'link_lista' => $video->youtube_publicado === 'si' ? $video->link_lista : null,
                    'social' => $video->socialPublications->map(fn ($publication) => [
                        'platform' => $publication->platform,
                        'titulo' => $publication->titulo ?: $video->titulo,
                        'link' => $publication->link,
                    ])->values(),
                ])->values(),
            ])
            ->values();

        return response()->json($videos);
    }

    public function checkYoutubeLink(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:255'],
            'video_id' => ['nullable', 'integer'],
        ]);
        $youtubeVideoId = $this->extractYoutubeVideoId($validated['url']);

        if ($youtubeVideoId === null) {
            return response()->json(['exists' => false]);
        }

        $currentVideoId = (int) ($validated['video_id'] ?? 0);
        $matches = $request->user()->videos()
            ->whereNotNull('youtube_link')
            ->where('youtube_link', 'like', "%{$youtubeVideoId}%")
            ->when($currentVideoId > 0, fn ($query) => $query->where('id', '!=', $currentVideoId))
            ->get(['id', 'titulo', 'youtube_link']);

        $match = $matches->first(fn (YoutubeJsonTracker $video) => $this->extractYoutubeVideoId($video->youtube_link) === $youtubeVideoId);

        return response()->json([
            'exists' => $match !== null,
            'id' => $match?->id,
            'titulo' => $match?->titulo,
            'link' => $match?->youtube_link,
            'edit_url' => $match ? route('videos.edit', $match) : null,
        ]);
    }

    private function extractYoutubeVideoId(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($host === 'youtu.be' || $host === 'www.youtu.be') {
            $videoId = explode('/', $path)[0] ?? '';
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            $segments = explode('/', $path);
            $videoId = ($segments[0] ?? '') === 'watch'
                ? ($query['v'] ?? '')
                : (in_array($segments[0] ?? '', ['shorts', 'embed', 'live'], true) ? ($segments[1] ?? '') : '');
        } else {
            return null;
        }

        return preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId) === 1 ? $videoId : null;
    }

    public function store(VideoStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['categoria'] = $request->user()->categoria_principal;
        $data = $this->normalizeCustomFields($data);
        $data = $this->setPublicationPeriod($data);
        $social = $data['social'] ?? [];
        unset($data['social']);
        $data = $this->storeThumbnail($request, $data);

        $video = $request->user()->videos()->create($data);
        $this->attachPlaylist($video, $data['titulo_lista'] ?? null, $data['link_lista'] ?? null);
        $this->saveSocialPublications($video, $social);

        return to_route('videos.index')->with('status', 'Video guardado correctamente.');
    }

    public function edit(Request $request, YoutubeJsonTracker $video): View
    {
        abort_unless($video->user_id === $request->user()->id, 404);
        $video->load('playlists');

        return view('videos.edit', compact('video'));
    }

    public function storePlaylist(Request $request, YoutubeJsonTracker $video): RedirectResponse
    {
        abort_unless($video->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'playlist_title' => ['nullable', 'string', 'max:255'],
            'playlist_url' => ['required', 'url', 'max:255'],
        ]);

        $playlist = $request->user()->playlists()->updateOrCreate(
            ['url' => $data['playlist_url']],
            ['name' => filled($data['playlist_title'] ?? null) ? $data['playlist_title'] : $data['playlist_url']],
        );
        $video->playlists()->syncWithoutDetaching([$playlist->id]);

        return to_route('videos.edit', $video)->with('status', 'Lista asociada al video correctamente.');
    }

    public function show(Request $request, YoutubeJsonTracker $video): View
    {
        abort_unless($video->user_id === $request->user()->id, 404);
        $video->load(['socialPublications', 'playlists']);

        return view('videos.show', compact('video'));
    }

    public function update(VideoStoreRequest $request, YoutubeJsonTracker $video): RedirectResponse
    {
        abort_unless($video->user_id === $request->user()->id, 404);
        $data = $request->validated();
        $data['categoria'] = $request->user()->categoria_principal;
        $data = $this->normalizeCustomFields($data);
        $data = $this->setPublicationPeriod($data);
        $social = $data['social'] ?? [];
        unset($data['social']);
        $data = $this->storeThumbnail($request, $data, $video);

        $video->update($data);
        $this->attachPlaylist($video, $data['titulo_lista'] ?? null, $data['link_lista'] ?? null);
        $this->saveSocialPublications($video, $social);

        return to_route('dashboard')->with('status', 'Video actualizado correctamente.');
    }

    public function destroy(Request $request, YoutubeJsonTracker $video): RedirectResponse
    {
        abort_unless($video->user_id === $request->user()->id, 404);
        $video->delete();

        return to_route('videos.index')->with('status', 'Video eliminado.');
    }

    private function saveSocialPublications(YoutubeJsonTracker $video, array $social): void
    {
        foreach (['facebook', 'tiktok', 'instagram'] as $platform) {
            $data = $social[$platform] ?? [];
            $data['publicado'] = $data['publicado'] ?? 'no';

            $video->socialPublications()->updateOrCreate(
                ['platform' => $platform],
                [
                    'titulo' => $data['publicado'] === 'si' ? ($data['titulo'] ?? null) : null,
                    'publicado' => $data['publicado'] ?? 'no',
                    'tipo' => $data['tipo'] ?? null,
                    'fecha' => $data['fecha'] ?? null,
                    'link' => $data['link'] ?? null,
                    'formato' => $data['formato'] ?? null,
                    'formato_guardado' => $data['formato_guardado'] ?? 'no',
                    'anio' => isset($data['fecha']) ? Carbon::parse($data['fecha'])->year : null,
                    'mes' => isset($data['fecha']) ? Carbon::parse($data['fecha'])->locale('es')->translatedFormat('F') : null,
                    'miniatura' => $this->storeUploadedFile($data['miniatura'] ?? null, $video->socialPublications()->where('platform', $platform)->first()?->miniatura),
                ],
            );
        }
    }

    private function attachPlaylist(YoutubeJsonTracker $video, ?string $title, ?string $url): void
    {
        if (blank($url)) {
            return;
        }

        $playlist = $video->user->playlists()->updateOrCreate(
            ['url' => $url],
            ['name' => filled($title) ? $title : $url],
        );
        $video->playlists()->syncWithoutDetaching([$playlist->id]);
    }

    private function setPublicationPeriod(array $data): array
    {
        if (($data['youtube_publicado'] ?? 'si') === 'no') {
            $data['fecha_subido'] = null;
            $data['fecha_publicacion'] = null;
            $data['titulo'] = null;
            $data['mes'] = null;
            $data['anio'] = null;
            $data['red_social'] = null;
            $data['formato'] = null;
            $data['tipo_video'] = null;
            $data['youtube_link'] = null;
            $data['titulo_lista'] = null;
            $data['link_lista'] = null;
            $data['youtube_ruta_editado'] = null;
            $data['youtube_formato_guardado'] = 'no';
            $data['publicado_comunidad'] = 'no';

            return $data;
        }

        if ($data['youtube_publicado'] === 'privado') {
            $data['fecha_publicacion'] = null;
            $data['mes'] = null;
            $data['anio'] = null;
            $data['red_social'] = null;
            $data['publicado_comunidad'] = 'no';

            return $data;
        }

        $publicationDate = Carbon::parse($data['fecha_publicacion']);
        $data['mes'] = $publicationDate->locale('es')->translatedFormat('F');
        $data['anio'] = $publicationDate->year;
        $data['tipo_video'] = $data['formato'];
        $data['red_social'] = 'YouTube';

        return $data;
    }

    private function normalizeCustomFields(array $data): array
    {
        if (($data['tipo_aparato'] ?? null) === 'Otro') {
            $data['tipo_aparato'] = $data['tipo_aparato_personalizado'];
        }
        if (($data['marca'] ?? null) === 'Otra') {
            $data['marca'] = $data['marca_personalizada'];
        }
        if (($data['kg'] ?? null) === 'personalizado') {
            $data['kg'] = $data['kg_personalizado'];
        }

        unset($data['tipo_aparato_personalizado'], $data['marca_personalizada'], $data['kg_personalizado']);

        return $data;
    }

    private function storeThumbnail(Request $request, array $data, ?YoutubeJsonTracker $video = null): array
    {
        if ($request->hasFile('youtube_miniatura')) {
            $data['youtube_miniatura'] = $this->storeUploadedFile($request->file('youtube_miniatura'), $video?->youtube_miniatura);
        }

        return $data;
    }

    private function storeUploadedFile(?UploadedFile $file, ?string $existingPath = null): ?string
    {
        if (! $file) {
            return $existingPath;
        }

        if ($existingPath) {
            Storage::disk('public')->delete($existingPath);
        }

        return $file->store('thumbnails', 'public');
    }
}