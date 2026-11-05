<x-app-layout>
    <x-slot name="header"><div class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm font-semibold uppercase tracking-wider text-red-600">Espacio de trabajo</p><h2 class="mt-1 text-2xl font-bold text-slate-900">Panel de videos</h2></div><span class="text-sm text-slate-500">{{ now()->format('d/m/Y') }}</span></div></x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl bg-slate-950 p-8 text-white shadow-sm sm:p-10"><div class="max-w-2xl"><p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-300">Centro de control</p><h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Hola, {{ Auth::user()->name }}.</h1><p class="mt-3 max-w-xl text-slate-300">Organiza tus videos de reparación, sus formatos y enlaces relacionados para encontrarlos sin perder tiempo.</p><div class="mt-7 flex flex-wrap gap-3"><a href="{{ route('videos.create') }}" class="rounded-md bg-red-600 px-4 py-3 text-sm font-semibold text-white hover:bg-red-700">Registrar video</a><a href="{{ route('videos.index') }}" class="rounded-md border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20">Ver historial</a></div></div></div>
            <div class="grid gap-5 md:grid-cols-3">
                <a href="{{ route('videos.create') }}" class="group rounded-xl border border-red-100 bg-red-50 p-6 transition hover:-translate-y-0.5 hover:shadow-md"><span class="text-2xl text-red-600">＋</span><h3 class="mt-4 font-bold text-slate-900">Nuevo registro</h3><p class="mt-1 text-sm text-slate-600">Guarda un video y sus referencias.</p><span class="mt-5 block text-sm font-semibold text-red-700">Abrir formulario →</span></a>
                <a href="#dashboard-search" class="group rounded-xl border border-blue-100 bg-blue-50 p-6 transition hover:-translate-y-0.5 hover:shadow-md"><span class="text-2xl text-blue-600">⌕</span><h3 class="mt-4 font-bold text-slate-900">Buscar referencias</h3><p class="mt-1 text-sm text-slate-600">Encuentra videos por modelo o tema.</p><span class="mt-5 block text-sm font-semibold text-blue-700">Buscar ahora →</span></a>
                <a href="{{ route('profile.edit') }}" class="group rounded-xl border border-purple-100 bg-purple-50 p-6 transition hover:-translate-y-0.5 hover:shadow-md"><span class="text-2xl text-purple-600">◉</span><h3 class="mt-4 font-bold text-slate-900">Tu cuenta</h3><p class="mt-1 text-sm text-slate-600">Administra tus datos de acceso.</p><span class="mt-5 block text-sm font-semibold text-purple-700">Ver perfil →</span></a>
                </div>

                <section class="rounded-xl border border-red-100 bg-white p-6 shadow-sm">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Videos por lista de reproducción</h3>
                        <p class="mt-1 text-sm text-slate-500">Selecciona una lista para ver todos los videos que tienes asociados.</p>
                    </div>
                    <form method="GET" action="{{ route('dashboard') }}" class="mt-4 flex flex-wrap items-end gap-3">
                        @if ($searchTerm !== '')
                            <input type="hidden" name="q" value="{{ $searchTerm }}">
                        @endif
                        <input type="hidden" name="periodo" value="{{ $period }}">
                        @if ($period === 'personalizado')
                            <input type="hidden" name="desde" value="{{ $from->format('Y-m-d') }}">
                            <input type="hidden" name="hasta" value="{{ $to->format('Y-m-d') }}">
                        @endif
                        <div class="min-w-0 flex-1">
                            <label for="playlist_id" class="block text-sm font-medium text-gray-700">Lista</label>
                            <select id="playlist_id" name="playlist_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                <option value="">Selecciona una lista</option>
                                @foreach ($playlists as $playlist)
                                    <option value="{{ $playlist->id }}" @selected($selectedPlaylist?->id === $playlist->id)>{{ $playlist->name }} ({{ $playlist->videos_count }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Mostrar videos</button>
                    </form>
                    @if ($playlists->isEmpty())
                        <p class="mt-3 text-sm text-slate-500">Aún no tienes listas registradas. Agrega una al registrar o editar un video.</p>
                    @elseif ($selectedPlaylist)
                        <article class="mt-5 rounded-lg border border-red-200 bg-red-50 p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h4 class="font-semibold text-red-950">{{ $selectedPlaylist->name }} · {{ $playlistVideos->count() }} videos</h4>
                                    <a href="{{ $selectedPlaylist->url }}" target="_blank" rel="noopener" class="mt-1 block break-all text-sm text-sky-700 hover:underline">{{ $selectedPlaylist->url }}</a>
                                </div>
                                @if ($playlistVideos->isNotEmpty())
                                    <button type="button" data-copy="{{ $playlistCopyText }}" class="shrink-0 rounded-md bg-slate-800 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-900">Copiar contenido para descripción</button>
                                @endif
                            </div>
                            @if ($playlistVideos->isEmpty())
                                <p class="mt-3 text-sm text-slate-500">Esta lista no tiene videos asociados.</p>
                            @else
                                <ul class="mt-3 divide-y divide-red-100">
                                    @foreach ($playlistVideos as $video)
                                        <li data-playlist-video="{{ $video->id }}" class="flex flex-wrap items-center justify-between gap-3 py-3">
                                            <div class="min-w-0">
                                                <a href="{{ route('videos.show', $video) }}" class="font-medium text-sky-700 hover:underline">{{ $video->titulo }}</a>
                                                <p class="mt-1 text-sm text-slate-500">{{ $video->tipo_aparato }} · {{ $video->marca }} · {{ $video->modelo ?: 'Sin modelo' }} · {{ $video->fecha_publicacion?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                                            </div>
                                            @if ($video->youtube_publicado === 'si' && $video->youtube_link)
                                                <a href="{{ $video->youtube_link }}" target="_blank" rel="noopener" class="text-sm font-semibold text-red-700 hover:underline">Abrir en YouTube</a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </article>
                    @endif
                </section>

                <section id="dashboard-search" class="rounded-xl border border-blue-100 bg-white p-6 shadow-sm">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Buscador de referencias</h3>
                        <p class="mt-1 text-sm text-slate-500">Da prioridad al tipo de aparato y la marca; en lavadoras, también a los kg. Busca en título, modelo comercial, tema del error y enlaces, sin necesitar el modelo exacto.</p>
                    </div>
                    <form method="GET" action="{{ route('dashboard') }}" class="mt-4 flex flex-wrap gap-3">
                        <input type="hidden" name="periodo" value="{{ $period }}">
                        @if ($period === 'personalizado')
                            <input type="hidden" name="desde" value="{{ $from->format('Y-m-d') }}">
                            <input type="hidden" name="hasta" value="{{ $to->format('Y-m-d') }}">
                        @endif
                        <label for="dashboard-search-query" class="sr-only">Buscar una palabra o frase en tus videos</label>
                        <input id="dashboard-search-query" type="search" name="q" value="{{ $searchTerm }}" maxlength="255" placeholder="Ej. reinicio lavadora Whirlpool de 17 kg Xpert System" class="min-w-0 flex-1 rounded-md border-gray-300 text-sm">
                        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Buscar</button>
                    </form>

                    @if ($searchPerformed)
                        <div class="mt-5" aria-live="polite">
                            <h4 class="text-sm font-semibold text-slate-700">
                                Resultados para “{{ $searchTerm }}”
                            </h4>
                            @if ($searchResults->isEmpty())
                                <p class="mt-2 text-sm text-slate-500">No se encontraron videos con esa palabra o frase.</p>
                            @else
                                <p class="mt-1 text-xs text-slate-500">Mostrando hasta 20 coincidencias recientes.</p>
                                <ul class="mt-2 divide-y divide-slate-100">
                                    @foreach ($searchResults as $video)
                                        <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                                            <div class="min-w-0">
                                                <p class="font-medium text-slate-800">{{ $video->titulo }}</p>
                                                <p class="mt-1 text-slate-500">{{ $video->tipo_aparato }} · {{ $video->marca }} · {{ $video->kg ? rtrim(rtrim(number_format((float) $video->kg, 2, '.', ''), '0'), '.') : 'Sin capacidad' }} kg · Modelo: {{ $video->modelo ?: 'Sin modelo' }}</p>
                                                <p class="mt-1 text-slate-500">Tema/error: {{ $video->tema_error ?: 'Sin tema' }} · {{ $video->fecha_publicacion?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button type="button" class="related-video-button font-semibold text-emerald-700 hover:text-emerald-900" data-video-id="{{ $video->id }}" data-modelo="{{ $video->modelo }}" data-tipo-aparato="{{ $video->tipo_aparato }}" data-marca="{{ $video->marca }}" data-kg="{{ $video->kg }}" data-tema-error="{{ $video->tema_error }}">Seleccionar</button>
                                                <a href="{{ route('videos.show', $video) }}" class="font-semibold text-sky-700">Ver video →</a>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif
                </section>
                @if ($dashboardReference)
                    <button
                        type="button"
                        class="related-video-button hidden"
                        data-auto-select="true"
                        data-related-limit="20"
                        data-include-reference="true"
                        data-video-id="{{ $dashboardReference->id }}"
                        data-modelo="{{ $dashboardReference->modelo }}"
                        data-tipo-aparato="{{ $dashboardReference->tipo_aparato }}"
                        data-marca="{{ $dashboardReference->marca }}"
                        data-kg="{{ $dashboardReference->kg }}"
                        data-tema-error="{{ $dashboardReference->tema_error }}"
                    >Mostrar referencias recientes</button>
                @endif
                @include('videos._related-search')

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <h3 class="text-lg font-bold text-slate-900">Resumen por periodo</h3>
                        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-end gap-3">
                            @if ($searchTerm !== '')
                                <input type="hidden" name="q" value="{{ $searchTerm }}">
                            @endif
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

<script>
    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            await navigator.clipboard.writeText(button.dataset.copy);
            const original = button.textContent;
            button.textContent = 'Copiado';
            setTimeout(() => { button.textContent = original; }, 1200);
        });
    });
</script>
