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
            $query->where(function ($videoQuery) use ($term) {
                $videoQuery->where('titulo', 'like', "%{$term}%")
                    ->orWhere('modelo', 'like', "%{$term}%")
                    ->orWhere('marca', 'like', "%{$term}%")
                    ->orWhere('tema_error', 'like', "%{$term}%");
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
                    ->unique('youtube_link')
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
                    ->unique('youtube_link')
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

    public function create(): View
    {
        return view('videos.create', ['video' => new YoutubeJsonTracker]);
    }

    public function relatedSearch(Request $request): JsonResponse
    {
        $model = trim((string) $request->query('modelo'));
        $type = trim((string) $request->query('tipo_aparato'));
        $brand = trim((string) $request->query('marca'));
        $currentId = $request->integer('video_id');

        if ($model === '' && ($type === '' || $brand === '')) {
            return response()->json([]);
        }

        $baseQuery = $request->user()->videos()
            ->with(['socialPublications' => fn ($query) => $query->whereIn('platform', ['facebook', 'tiktok'])->where('publicado', 'si')->whereNotNull('link')])
            ->select(['id', 'tipo_aparato', 'marca', 'kg', 'modelo', 'titulo', 'youtube_link', 'titulo_lista', 'link_lista'])
            ->where(function ($query) {
                $query->whereNotNull('youtube_link')
                    ->orWhereHas('socialPublications', fn ($socialQuery) => $socialQuery->whereIn('platform', ['facebook', 'tiktok'])->where('publicado', 'si')->whereNotNull('link'));
            })
            ->when($currentId > 0, fn ($query) => $query->where('id', '!=', $currentId));

        $sameModelVideos = (clone $baseQuery)->where('modelo', $model)->latest()->get();
        $fallbackVideos = collect();
        $refrigeratorTypes = ['Nevera', 'Nevera frio seco', 'Frigobar', 'Camara', 'Nevera pequeña'];
        $fallbackTypes = in_array($type, $refrigeratorTypes, true)
            ? ['Lavadora']
            : $refrigeratorTypes;

        if ($sameModelVideos->isEmpty() && $brand !== '') {
            $fallbackVideos = (clone $baseQuery)
                ->where('marca', $brand)
                ->whereIn('tipo_aparato', $fallbackTypes)
                ->latest()
                ->get();
        }

        if ($sameModelVideos->isEmpty() && $fallbackVideos->isEmpty()) {
            $washingMachineModel = (clone $baseQuery)
                ->where('tipo_aparato', 'Lavadora')
                ->where('modelo', '!=', $model)
                ->inRandomOrder()
                ->value('modelo');

            if ($washingMachineModel) {
                $fallbackVideos = (clone $baseQuery)->where('modelo', $washingMachineModel)->latest()->get();
            }
        }

        $videos = $sameModelVideos
            ->concat($fallbackVideos)
            ->unique('youtube_link')
            ->values()
            ->groupBy(fn (YoutubeJsonTracker $video) => trim((string) $video->modelo) ?: 'Sin modelo')
            ->map(fn ($modelVideos, string $model) => [
                'modelo' => $model,
                'videos' => $modelVideos->take(8)->map(fn (YoutubeJsonTracker $video) => [
                    'id' => $video->id,
                    'titulo' => $video->titulo,
                    'link' => $video->youtube_link,
                    'titulo_lista' => $video->titulo_lista,
                    'link_lista' => $video->link_lista,
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
        $this->saveSocialPublications($video, $social);

        return to_route('dashboard')->with('status', 'Video guardado correctamente.');
    }

    public function edit(Request $request, YoutubeJsonTracker $video): View
    {
        abort_unless($video->user_id === $request->user()->id, 404);

        return view('videos.edit', compact('video'));
    }

    public function show(Request $request, YoutubeJsonTracker $video): View
    {
        abort_unless($video->user_id === $request->user()->id, 404);
        $video->load('socialPublications');

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

    private function setPublicationPeriod(array $data): array
    {
        if (($data['youtube_publicado'] ?? 'si') !== 'si') {
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