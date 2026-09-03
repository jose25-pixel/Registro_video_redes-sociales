<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->string('periodo')->toString() ?: 'todos';
        $allowedPeriods = ['todos', 'mes', 'trimestre', 'anio', 'personalizado'];
        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'todos';
        }

        [$from, $to] = $this->resolveRange($period, $request);

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

        return view('dashboard', [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'total' => $total,
            'porRedSocial' => $porRedSocial,
            'recientes' => $recientes,
        ]);
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
