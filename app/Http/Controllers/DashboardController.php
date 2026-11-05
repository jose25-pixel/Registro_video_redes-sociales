<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'playlist_id' => [
                'nullable',
                'integer',
                Rule::exists('playlists', 'id')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
        ]);
        $searchTerm = $request->string('q')->trim()->toString();
        $playlists = $request->user()->playlists()->withCount('videos')->orderBy('name')->get();
        $selectedPlaylist = $playlists->firstWhere('id', $request->integer('playlist_id'));
        $playlistVideos = $selectedPlaylist
            ? $selectedPlaylist->videos()
                ->where('user_id', $request->user()->id)
                ->latest('fecha_publicacion')
                ->get()
            : collect();
        $playlistCopyText = $selectedPlaylist
            ? "Lista: {$selectedPlaylist->name}\n{$selectedPlaylist->url}\n\nVideos:\n\n".$playlistVideos
                ->map(fn ($video) => '- '.$video->titulo.($video->youtube_publicado === 'si' && $video->youtube_link ? "\n".$video->youtube_link : ''))
                ->implode("\n\n")
            : null;

        $period = $request->string('periodo')->toString() ?: 'todos';
        $allowedPeriods = ['todos', 'mes', 'trimestre', 'anio', 'personalizado'];
        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'todos';
        }

        [$from, $to] = $this->resolveRange($period, $request);

        $searchResults = collect();
        $searchPerformed = $searchTerm !== '';
        preg_match('/(?<!\d)(\d+(?:[.,]\d+)?)\s*(?:kg|kilos?)(?!\p{L})/iu', $searchTerm, $capacityMatch);
        $capacity = isset($capacityMatch[1]) ? (float) str_replace(',', '.', $capacityMatch[1]) : null;
        $terms = preg_split('/[^\p{L}\p{N}]+/u', $searchTerm, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $ignoredTerms = ['de', 'del', 'la', 'las', 'el', 'los', 'un', 'una', 'y', 'kg', 'kilo', 'kilos'];
        $terms = array_values(array_filter($terms, fn (string $term) => ! in_array(mb_strtolower($term), $ignoredTerms, true)));
        if ($searchPerformed && $terms !== []) {
            $searchQuery = $request->user()->videos();
            if ($capacity !== null) {
                $searchQuery->where('kg', $capacity);
            }
            $searchQuery->where(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    foreach ($this->searchTermVariants($term) as $variant) {
                        foreach (['titulo', 'modelo', 'modelo_comercial', 'marca', 'tipo_aparato', 'tecnologia', 'tema_error', 'descripcion', 'youtube_link', 'link_lista', 'titulo_lista'] as $field) {
                            $query->orWhere($field, 'like', "%{$variant}%");
                        }
                        $query->orWhereHas('socialPublications', fn ($socialQuery) => $socialQuery
                            ->where('titulo', 'like', "%{$variant}%")
                            ->orWhere('link', 'like', "%{$variant}%"));
                    }
                }
            });

            $searchFields = ['titulo', 'modelo', 'modelo_comercial', 'marca', 'tipo_aparato', 'tecnologia', 'tema_error', 'descripcion', 'youtube_link', 'link_lista', 'titulo_lista'];
            $requestedAppliance = $this->applianceFamily($searchTerm);
            $searchResults = $searchQuery->get()
                ->map(function ($video) use ($terms, $capacity, $searchFields, $requestedAppliance) {
                    $searchableText = mb_strtolower(implode(' ', array_map(
                        fn (string $field) => (string) $video->{$field},
                        $searchFields
                    )));
                    $searchableBrand = mb_strtolower((string) $video->marca);
                    $matchedTerms = collect($terms)
                        ->filter(fn (string $term) => collect($this->searchTermVariants($term))
                            ->contains(fn (string $variant) => str_contains($searchableText, mb_strtolower($variant))))
                        ->count();

                    return [
                        'video' => $video,
                        'capacityMatch' => $capacity !== null && (float) $video->kg === $capacity,
                        'applianceMatch' => $requestedAppliance !== null && $this->applianceFamily((string) $video->tipo_aparato) === $requestedAppliance,
                        'brandMatch' => collect($terms)
                            ->filter(fn (string $term) => collect($this->searchTermVariants($term))
                                ->contains(fn (string $variant) => mb_strlen($variant) >= 3 && str_contains($searchableBrand, mb_strtolower($variant))))
                            ->isNotEmpty(),
                        'matchedTerms' => $matchedTerms,
                    ];
                })
                ->sort(function (array $left, array $right) {
                    return ($right['capacityMatch'] <=> $left['capacityMatch'])
                        ?: ($right['applianceMatch'] <=> $left['applianceMatch'])
                        ?: ($right['brandMatch'] <=> $left['brandMatch'])
                        ?: ($right['matchedTerms'] <=> $left['matchedTerms'])
                        ?: ($right['video']->id <=> $left['video']->id);
                })
                ->take(20)
                ->pluck('video')
                ->values();
        }

        $baseQuery = $request->user()->videos();
        if ($from && $to) {
            $baseQuery->whereBetween('fecha_publicacion', [$from, $to]);
        }

        $total = (clone $baseQuery)->count();
        $porRedSocial = [
            'youtube' => (clone $baseQuery)->where('red_social', 'YouTube')->count(),
            'facebook' => (clone $baseQuery)->whereHas('socialPublications', fn ($q) => $q->where('platform', 'facebook')->where('publicado', 'si'))->count(),
            'tiktok' => (clone $baseQuery)->whereHas('socialPublications', fn ($q) => $q->where('platform', 'tiktok')->where('publicado', 'si'))->count(),
            'instagram' => (clone $baseQuery)->whereHas('socialPublications', fn ($q) => $q->where('platform', 'instagram')->where('publicado', 'si'))->count(),
        ];
        $recientes = (clone $baseQuery)->latest('fecha_publicacion')->limit(5)->get();
        $dashboardReference = $request->user()->videos()->latest()->first();

        return view('dashboard', [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'total' => $total,
            'porRedSocial' => $porRedSocial,
            'recientes' => $recientes,
            'searchTerm' => $searchTerm,
            'searchResults' => $searchResults,
            'searchPerformed' => $searchPerformed,
            'dashboardReference' => $dashboardReference,
            'playlists' => $playlists,
            'selectedPlaylist' => $selectedPlaylist,
            'playlistVideos' => $playlistVideos,
            'playlistCopyText' => $playlistCopyText,
        ]);
    }

    private function searchTermVariants(string $term): array
    {
        $variants = [$term];
        if (str_ends_with($term, 'io')) {
            $variants[] = substr($term, 0, -1);
        }
        foreach (['ando', 'iendo', 'ados', 'adas', 'idos', 'idas', 'ado', 'ada', 'ido', 'ida', 'ar', 'er', 'ir', 'ia', 'os', 'as', 'o', 'a'] as $suffix) {
            if (strlen($term) - strlen($suffix) >= 4 && str_ends_with($term, $suffix)) {
                $variants[] = substr($term, 0, -strlen($suffix));
                break;
            }
        }

        return $variants;
    }

    private function applianceFamily(string $text): ?string
    {
        $text = mb_strtolower($text);

        if (str_contains($text, 'lavador') || str_contains($text, 'washer')) {
            return 'lavadora';
        }
        foreach (['nevera', 'refrigerador', 'refrigeradora', 'frigobar', 'camara'] as $refrigeratorTerm) {
            if (str_contains($text, $refrigeratorTerm)) {
                return 'refrigeradora';
            }
        }

        return null;
    }

    private function resolveRange(string $period, Request $request): array
    {
        $to = Carbon::now()->endOfDay();

        return match ($period) {
            'todos' => [null, null],
            'trimestre' => [Carbon::now()->subMonths(3)->startOfDay(), $to],
            'anio' => [Carbon::now()->subYear()->startOfDay(), $to],
            'personalizado' => [
                $request->filled('desde') ? Carbon::parse($request->string('desde')->toString())->startOfDay() : Carbon::now()->subMonth()->startOfDay(),
                $request->filled('hasta') ? Carbon::parse($request->string('hasta')->toString())->endOfDay() : $to,
            ],
            'mes' => [Carbon::now()->subMonth()->startOfDay(), $to],
            default => [null, null],
        };
    }
}
