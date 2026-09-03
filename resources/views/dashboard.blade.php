<x-app-layout>
    <x-slot name="header"><div class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm font-semibold uppercase tracking-wider text-red-600">Espacio de trabajo</p><h2 class="mt-1 text-2xl font-bold text-slate-900">Panel de videos</h2></div><span class="text-sm text-slate-500">{{ now()->format('d/m/Y') }}</span></div></x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl bg-slate-950 p-8 text-white shadow-sm sm:p-10"><div class="max-w-2xl"><p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-300">Centro de control</p><h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Hola, {{ Auth::user()->name }}.</h1><p class="mt-3 max-w-xl text-slate-300">Organiza tus videos de reparación, sus formatos y enlaces relacionados para encontrarlos sin perder tiempo.</p><div class="mt-7 flex flex-wrap gap-3"><a href="{{ route('videos.create') }}" class="rounded-md bg-red-600 px-4 py-3 text-sm font-semibold text-white hover:bg-red-700">Registrar video</a><a href="{{ route('videos.index') }}" class="rounded-md border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20">Ver historial</a></div></div></div>
            <div class="grid gap-5 md:grid-cols-3">
                <a href="{{ route('videos.create') }}" class="group rounded-xl border border-red-100 bg-red-50 p-6 transition hover:-translate-y-0.5 hover:shadow-md"><span class="text-2xl text-red-600">＋</span><h3 class="mt-4 font-bold text-slate-900">Nuevo registro</h3><p class="mt-1 text-sm text-slate-600">Guarda un video y sus referencias.</p><span class="mt-5 block text-sm font-semibold text-red-700">Abrir formulario →</span></a>
                <a href="{{ route('videos.index') }}" class="group rounded-xl border border-blue-100 bg-blue-50 p-6 transition hover:-translate-y-0.5 hover:shadow-md"><span class="text-2xl text-blue-600">⌕</span><h3 class="mt-4 font-bold text-slate-900">Buscar referencias</h3><p class="mt-1 text-sm text-slate-600">Encuentra por aparato, modelo o formato.</p><span class="mt-5 block text-sm font-semibold text-blue-700">Ver historial →</span></a>
                <a href="{{ route('profile.edit') }}" class="group rounded-xl border border-purple-100 bg-purple-50 p-6 transition hover:-translate-y-0.5 hover:shadow-md"><span class="text-2xl text-purple-600">◉</span><h3 class="mt-4 font-bold text-slate-900">Tu cuenta</h3><p class="mt-1 text-sm text-slate-600">Administra tus datos de acceso.</p><span class="mt-5 block text-sm font-semibold text-purple-700">Ver perfil →</span></a>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <h3 class="text-lg font-bold text-slate-900">Resumen por periodo</h3>
                        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-end gap-3">
                            <div class="flex flex-wrap gap-2">
                                @foreach (['todos' => 'Todos', 'mes' => 'Último mes', 'trimestre' => 'Cada 3 meses', 'anio' => 'Último año', 'personalizado' => 'Personalizado'] as $value => $label)
                                    <button type="submit" name="periodo" value="{{ $value }}" class="rounded-md px-3 py-2 text-sm font-semibold {{ $period === $value ? 'bg-red-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ $label }}</button>
                                @endforeach
                            </div>
                            @if ($period === 'personalizado')
                                <div class="flex items-end gap-2">
                                    <div><label class="block text-xs font-medium text-slate-500">Desde</label><input type="date" name="desde" value="{{ $from->format('Y-m-d') }}" class="mt-1 rounded-md border-gray-300 text-sm"></div>
                                    <div><label class="block text-xs font-medium text-slate-500">Hasta</label><input type="date" name="hasta" value="{{ $to->format('Y-m-d') }}" class="mt-1 rounded-md border-gray-300 text-sm"></div>
                                    <input type="hidden" name="periodo" value="personalizado">
                                    <button type="submit" class="rounded-md bg-slate-800 px-3 py-2 text-sm font-semibold text-white">Aplicar</button>
                                </div>
                            @endif
                        </form>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">@if ($from && $to) Del {{ $from->format('d/m/Y') }} al {{ $to->format('d/m/Y') }}, según fecha de publicación. @else Todos tus videos registrados, sin filtro de fecha. @endif</p>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div class="rounded-lg bg-slate-50 p-4"><p class="text-xs uppercase text-slate-500">Total videos</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ $total }}</p></div>
                        <div class="rounded-lg bg-red-50 p-4"><p class="text-xs uppercase text-red-600">YouTube</p><p class="mt-1 text-2xl font-bold text-red-900">{{ $porRedSocial['youtube'] }}</p></div>
                        <div class="rounded-lg bg-blue-50 p-4"><p class="text-xs uppercase text-blue-600">Facebook</p><p class="mt-1 text-2xl font-bold text-blue-900">{{ $porRedSocial['facebook'] }}</p></div>
                        <div class="rounded-lg bg-purple-50 p-4"><p class="text-xs uppercase text-purple-600">TikTok</p><p class="mt-1 text-2xl font-bold text-purple-900">{{ $porRedSocial['tiktok'] }}</p></div>
                        <div class="rounded-lg bg-pink-50 p-4"><p class="text-xs uppercase text-pink-600">Instagram</p><p class="mt-1 text-2xl font-bold text-pink-900">{{ $porRedSocial['instagram'] }}</p></div>
                    </div>

                    <div class="mt-6">
                        <h4 class="text-sm font-semibold text-slate-700">Últimos registros del periodo</h4>
                        @if ($recientes->isEmpty())
                            <p class="mt-2 text-sm text-slate-500">Sin videos publicados en este rango.</p>
                        @else
                            <ul class="mt-2 divide-y divide-slate-100">
                                @foreach ($recientes as $video)
                                    <li class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm"><span class="font-medium text-slate-800">{{ $video->titulo }}</span><span class="text-slate-500">{{ $video->tipo_aparato }} · {{ $video->modelo }} · {{ $video->fecha_publicacion?->format('d/m/Y') }}</span><a href="{{ route('videos.show', $video) }}" class="font-semibold text-sky-700">Ver →</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
