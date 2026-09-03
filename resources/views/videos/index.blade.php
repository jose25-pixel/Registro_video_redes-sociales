<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Registro de videos</h2>
            <a href="{{ route('videos.create') }}" class="rounded-md bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800">Nuevo video</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div id="print-references" class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Videos registrados</p><p class="mt-1 text-3xl font-bold text-sky-700">{{ $total }}</p></div>
                <div class="rounded-lg bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Ultimo titulo</p><p class="mt-1 truncate font-semibold text-gray-900">{{ $latest?->titulo ?? 'Sin registros' }}</p></div>
                <div class="rounded-lg bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Tema mas reciente</p><p class="mt-1 truncate font-semibold text-gray-900">{{ $latest?->tema_error ?? 'Sin registros' }}</p></div>
            </div>

            @if ($latest)
                <div class="rounded-lg border border-red-200 bg-red-50 p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div><h3 class="text-lg font-semibold text-red-950">Ultimo registro de YouTube</h3><p class="mt-1 text-sm text-red-800">Referencia para buscar videos relacionados por aparato, modelo y KG.</p></div>
                        <button type="button" onclick="window.print()" class="rounded-md border border-red-300 bg-white px-3 py-2 text-sm font-semibold text-red-800 hover:bg-red-100">Imprimir referencias</button>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        @foreach ([['Titulo', $latest->titulo], ['Video', $latest->youtube_link], ['Lista', $latest->titulo_lista && $latest->link_lista ? $latest->titulo_lista.' | '.$latest->link_lista : 'Sin lista relacionada']] as [$label, $text])
                            <div class="rounded-md border border-red-200 bg-white p-3"><p class="text-xs font-semibold uppercase text-gray-500">{{ $label }}</p><p class="mt-1 break-words text-sm text-gray-900">{{ $text }}</p>@if ($text !== 'Sin lista relacionada')<button type="button" data-copy="{{ $text }}" class="mt-2 text-xs font-semibold text-red-700 hover:underline">Copiar</button>@endif</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-lg font-semibold text-gray-900">Busqueda manual</h3>
                <form method="GET" class="grid gap-3 md:grid-cols-2 lg:grid-cols-[1fr_190px_190px_190px_auto]">
                    <input name="q" value="{{ request('q') }}" placeholder="Buscar por titulo, modelo, marca o error" class="rounded-md border-gray-300 text-sm">
                    <select name="tipo_aparato" class="rounded-md border-gray-300 text-sm">
                        <option value="">Todos los aparatos</option>
                        @foreach ($types as $type)<option value="{{ $type }}" @selected(request('tipo_aparato') === $type)>{{ $type }}</option>@endforeach
                    </select>
                    <select name="red_social" class="rounded-md border-gray-300 text-sm">
                        <option value="">Todas las redes</option>
                        <option value="youtube" @selected(request('red_social') === 'youtube')>YouTube</option>
                        <option value="facebook" @selected(request('red_social') === 'facebook')>Facebook</option>
                        <option value="tiktok" @selected(request('red_social') === 'tiktok')>TikTok</option>
                        <option value="instagram" @selected(request('red_social') === 'instagram')>Instagram</option>
                    </select>
                    <select name="formato" class="rounded-md border-gray-300 text-sm">
                        <option value="">Todos los formatos</option>
                        @foreach (['Short', 'Reel', 'Video normal', 'Video normal corto', 'En vivo'] as $format)<option value="{{ $format }}" @selected(request('formato') === $format)>{{ $format }}</option>@endforeach
                    </select>
                    <button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-900">Filtrar</button>
                </form>
            </div>

            @if ($suggestionContext !== '')
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                    <h3 class="text-lg font-semibold text-emerald-950">Referencias automaticas para copiar</h3>
                    <p class="mt-1 text-sm text-emerald-800">Se muestran solas al guardar. Incluye el modelo principal y los modelos relacionados por marca, aparato y tecnologia. Cada modelo muestra hasta 8 videos.</p>
                    @php($youtubeSuggestionsByModel = $suggestions->filter(fn ($suggestion) => $suggestion->youtube_link)->groupBy(fn ($suggestion) => trim((string) $suggestion->modelo) ?: 'Sin modelo'))
                    @if ($youtubeSuggestionsByModel->isNotEmpty())
                        <div class="mt-4">
                            <h4 class="font-semibold text-gray-900">YouTube</h4>
                            @php($copyYoutubeText = '')
                            @foreach ($youtubeSuggestionsByModel as $model => $modelVideos)
                                @php($modelVideos = $modelVideos->take(8))
                                @php($copyYoutubeText .= "Modelo: {$model}\n\n")
                                @foreach ($modelVideos as $video)
                                    @if ($video->link_lista && !in_array($video->link_lista, $copiedPlaylists ?? [], true))
                                        @php($copiedPlaylists[] = $video->link_lista)
                                        @php($copyYoutubeText .= "Lista: ".($video->titulo_lista ?? 'Sin titulo')."\n{$video->link_lista}\n\n")
                                    @endif
                                @endforeach
                                @foreach ($modelVideos as $video) @php($copyYoutubeText .= "- {$video->titulo}\n{$video->youtube_link}\n\n") @endforeach
                            @endforeach
                            <article class="mt-3 rounded-md border border-red-200 bg-red-50 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2"><h5 class="font-semibold text-red-950">Videos relacionados</h5><button type="button" data-copy="{{ $copyYoutubeText }}" class="rounded-md bg-white px-2 py-1 text-xs font-semibold text-gray-800 shadow-sm ring-1 ring-gray-300 hover:bg-gray-100">Copiar enlaces</button></div>
                                <div class="mt-4 space-y-7">
                                    @foreach ($youtubeSuggestionsByModel as $model => $modelVideos)
                                        @php($modelVideos = $modelVideos->take(8))
                                        <section class="border-b border-red-200 pb-6 last:border-0 last:pb-0">
                                            <h6 class="font-semibold text-gray-900">Modelo: {{ $model }}</h6>
                                    @php($copiedPlaylists = [])
                                            <div class="mt-4 space-y-5">
                                            @php($shownPlaylists = [])
                                            @foreach ($modelVideos as $video)
                                                @if ($video->link_lista && !in_array($video->link_lista, $shownPlaylists, true))
                                                    @php($shownPlaylists[] = $video->link_lista)
                                                    <div class="border-b border-red-100 pb-4 text-xs"><p class="font-medium text-gray-700">Lista: {{ $video->titulo_lista ?? 'Sin titulo' }}</p><a href="{{ $video->link_lista }}" target="_blank" rel="noopener" class="mt-1 block break-all text-sky-700 hover:underline">{{ $video->link_lista }}</a></div>
                                                @endif
                                            @endforeach
                                            @foreach ($modelVideos as $video)
                                                <div class="border-b border-red-100 pb-4 last:border-0 last:pb-0"><p class="text-sm font-medium text-gray-900">{{ $video->titulo }}</p><a href="{{ $video->youtube_link }}" target="_blank" rel="noopener" class="mt-1 block break-all text-xs text-sky-700 hover:underline">{{ $video->youtube_link }}</a></div>
                                            @endforeach
                                            </div>
                                        </section>
                                    @endforeach
                                </div>
                            </article>
                        </div>
                    @endif
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        @foreach (['facebook' => ['Facebook', 'border-blue-200 bg-blue-50'], 'tiktok' => ['TikTok', 'border-purple-200 bg-purple-50'], 'instagram' => ['Instagram', 'border-pink-200 bg-pink-50']] as $platform => [$label, $classes])
                            @php($socialVideosByModel = $suggestions->map(fn ($suggestion) => ['video' => $suggestion, 'publication' => $suggestion->socialPublications->firstWhere('platform', $platform)])->filter(fn ($entry) => $entry['publication']?->publicado === 'si' && $entry['publication']?->link)->groupBy(fn ($entry) => trim((string) $entry['video']->modelo) ?: 'Sin modelo'))
                            @foreach ($socialVideosByModel as $model => $entries)
                                @php($entries = $entries->take(8))
                                @php($copySocialText = "{$label}: {$model}\n\n")
                                @foreach ($entries as $entry) @php($copySocialText .= "- ".($entry['publication']->titulo ?: $entry['video']->titulo)."\n{$entry['publication']->link}\n") @endforeach
                                <section class="rounded-md border {{ $classes }} p-4"><div class="flex flex-wrap items-center justify-between gap-2"><h4 class="font-semibold text-gray-900">{{ $label }}: {{ $model }}</h4><button type="button" data-copy="{{ $copySocialText }}" class="rounded-md bg-white px-2 py-1 text-xs font-semibold text-gray-800 shadow-sm ring-1 ring-gray-300 hover:bg-gray-100">Copiar tarjeta</button></div><div class="mt-3 space-y-3">@foreach ($entries as $entry)<div class="border-b border-gray-200 pb-2 last:border-0"><p class="text-sm font-medium text-gray-900">{{ $entry['publication']->titulo ?: $entry['video']->titulo }}</p><a href="{{ $entry['publication']->link }}" target="_blank" rel="noopener" class="break-all text-xs text-sky-700 hover:underline">{{ $entry['publication']->link }}</a></div>@endforeach</div></section>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="px-6 py-3">Titulo</th><th class="px-6 py-3">Categoria</th><th class="px-6 py-3">Aparato</th><th class="px-6 py-3">Modelo</th><th class="px-6 py-3">Publicacion YouTube</th><th class="px-6 py-3">Facebook</th><th class="px-6 py-3">Acciones</th></tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($videos as $video)
                                @php($facebook = $video->socialPublications->firstWhere('platform', 'facebook'))
                                <tr><td class="px-6 py-4 font-medium text-gray-900">{{ $video->titulo }}</td><td class="px-6 py-4">{{ $video->categoria }} / {{ $video->subcategoria }}</td><td class="px-6 py-4">{{ $video->tipo_aparato }} / {{ $video->marca }}</td><td class="px-6 py-4">{{ $video->modelo }}</td><td class="px-6 py-4">{{ $video->fecha_publicacion?->format('d/m/Y') ?? 'Pendiente' }}</td><td class="px-6 py-4">{{ $video->mes }} / {{ $video->anio }}</td><td class="px-6 py-4">{{ $facebook?->mes ?? '-' }} / {{ $facebook?->anio ?? '-' }}</td><td class="px-6 py-4"><button type="button" class="related-video-button font-semibold text-emerald-700 hover:text-emerald-900" data-video-id="{{ $video->id }}" data-modelo="{{ $video->modelo }}" data-tipo-aparato="{{ $video->tipo_aparato }}" data-marca="{{ $video->marca }}">Seleccionar</button><a class="ml-3 font-semibold text-sky-700 hover:text-sky-900" href="{{ route('videos.show', $video) }}">Ver informacion</a><a class="ml-3 font-semibold text-sky-700 hover:text-sky-900" href="{{ route('videos.edit', $video) }}">Editar</a><form class="ml-3 inline" method="POST" action="{{ route('videos.destroy', $video) }}" onsubmit="return confirm('¿Eliminar este video?')">@csrf @method('DELETE')<button class="font-semibold text-red-700 hover:text-red-900">Eliminar</button></form></td></tr>
                            @empty
                                <tr><td colspan="8" class="px-6 py-10 text-center text-gray-500">Aun no hay videos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($videos->hasPages())<div class="border-t border-gray-200 px-6 py-4">{{ $videos->links() }}</div>@endif
            </div>
            <section id="related-videos-panel" class="hidden rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                <h3 class="text-lg font-semibold text-emerald-950">Videos relacionados</h3>
                <p id="related-videos-context" class="mt-1 text-sm text-emerald-800"></p>
                <div id="related-videos-results" class="mt-4 grid gap-3 md:grid-cols-2"></div>
            </section>
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

    document.querySelectorAll('.related-video-button').forEach((button) => {
        button.addEventListener('click', async () => {
            const panel = document.getElementById('related-videos-panel');
            const context = document.getElementById('related-videos-context');
            const results = document.getElementById('related-videos-results');
            const params = new URLSearchParams({
                video_id: button.dataset.videoId,
                modelo: button.dataset.modelo,
                tipo_aparato: button.dataset.tipoAparato,
                marca: button.dataset.marca,
            });

            panel.classList.remove('hidden');
            context.textContent = `Relacionados con ${button.dataset.marca} ${button.dataset.tipoAparato}, modelo ${button.dataset.modelo}`;
            results.innerHTML = '<p class="text-sm text-gray-600">Buscando videos relacionados...</p>';
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });

            try {
                const response = await fetch(`{{ route('videos.related-search') }}?${params}`);
                const modelGroups = await response.json();
                results.innerHTML = '';
                if (!modelGroups.length) {
                    results.innerHTML = '<p class="text-sm text-gray-600">No hay otros videos relacionados registrados.</p>';
                    return;
                }

                const renderPlatform = (platform, label) => {
                    const platformResults = document.createElement('section');
                    platformResults.className = 'col-span-full';
                    platformResults.innerHTML = `<h4 class="font-semibold text-gray-900">${label}</h4><div class="mt-3 grid gap-3 md:grid-cols-2"></div>`;
                    const cards = platformResults.querySelector('div');

                    modelGroups.forEach((modelGroup) => {
                        const publications = platform === 'youtube'
                            ? modelGroup.videos.filter((video) => video.link).map((video) => ({ titulo: video.titulo, link: video.link, tituloLista: video.titulo_lista, linkLista: video.link_lista }))
                            : modelGroup.videos.flatMap((video) => video.social.filter((publication) => publication.platform === platform));
                        if (!publications.length) return;

                        const card = document.createElement('article');
                        card.className = 'rounded-md border border-gray-200 bg-white p-4';
                        card.innerHTML = '<div class="flex flex-wrap items-center justify-between gap-2"><h5 class="font-semibold text-gray-900"></h5><button type="button" class="rounded-md bg-white px-2 py-1 text-xs font-semibold text-gray-800 shadow-sm ring-1 ring-gray-300 hover:bg-gray-100">Copiar tarjeta</button></div><div class="mt-3 space-y-3"></div>';
                        card.querySelector('h5').textContent = modelGroup.modelo;
                        const items = card.querySelector('div.mt-3');
                        const copyLines = [`${label}: ${modelGroup.modelo}`];

                        if (platform === 'youtube') {
                            const shownPlaylists = new Set();
                            publications.forEach((publication) => {
                                if (!publication.linkLista || shownPlaylists.has(publication.linkLista)) return;
                                shownPlaylists.add(publication.linkLista);
                                const playlist = document.createElement('div');
                                playlist.className = 'border-b border-gray-100 pb-3';
                                playlist.innerHTML = '<p                                 <footer class="text-sm text-slate-500">&copy; 2026 Registro Video. Desarrollado por Entornos Web y PN Cruz Zabaleta.</footer>class="text-xs font-semibold uppercase text-gray-500">Lista de reproduccion</p><p class="mt-1 text-sm text-gray-800"></p><a class="mt-1 block break-all text-sm text-sky-700 underline" target="_blank" rel="noopener"></a>';
                                playlist.querySelector('p:nth-child(2)').textContent = publication.tituloLista || 'Sin titulo';
                                const playlistLink = playlist.querySelector('a');
                                playlistLink.href = publication.linkLista;
                                playlistLink.textContent = publication.linkLista;
                                copyLines.push(`Lista: ${publication.tituloLista || 'Sin titulo'}\n${publication.linkLista}`);
                                items.appendChild(playlist);
                            });
                        }

                        publications.slice(0, 8).forEach((publication) => {
                            const item = document.createElement('div');
                            item.className = 'border-b border-gray-100 pb-3 last:border-0';
                            item.innerHTML = '<p class="font-medium text-gray-900"></p><a class="mt-1 block break-all text-sm text-sky-700 underline" target="_blank" rel="noopener"></a>';
                            item.querySelector('p').textContent = publication.titulo;
                            const link = item.querySelector('a');
                            link.href = publication.link;
                            link.textContent = publication.link;
                            copyLines.push(`- ${publication.titulo}\n${publication.link}`);
                            items.appendChild(item);
                        });

                        const copyButton = card.querySelector('button');
                        copyButton.addEventListener('click', async () => {
                            await navigator.clipboard.writeText(copyLines.join('\n'));
                            copyButton.textContent = 'Copiado';
                            setTimeout(() => { copyButton.textContent = 'Copiar tarjeta'; }, 1200);
                        });
                        cards.appendChild(card);
                    });

                    if (cards.childElementCount) results.appendChild(platformResults);
                };

                renderPlatform('youtube', 'YouTube');
                renderPlatform('facebook', 'Facebook');
                renderPlatform('tiktok', 'TikTok');
            } catch (error) {
                results.innerHTML = '<p class="text-sm text-red-700">No se pudieron cargar los videos relacionados.</p>';
            }
        });
    });
</script>