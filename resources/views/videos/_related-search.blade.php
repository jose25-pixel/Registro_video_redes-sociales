<section id="related-videos-panel" class="hidden rounded-lg border border-emerald-200 bg-emerald-50 p-5">
    <h3 class="text-lg font-semibold text-emerald-950">Videos relacionados</h3>
    <p id="related-videos-context" class="mt-1 text-sm text-emerald-800"></p>
    <div id="related-videos-results" class="mt-4 grid gap-3 md:grid-cols-2"></div>
</section>

<script>
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
                kg: button.dataset.kg,
                tema_error: button.dataset.temaError,
                limit: button.dataset.relatedLimit || '10',
                incluir_referencia: button.dataset.includeReference === 'true' ? '1' : '0',
            });

            panel.classList.remove('hidden');
            const limit = button.dataset.relatedLimit || '10';
            context.textContent = `Hasta ${limit} enlaces relacionados con ${button.dataset.marca} ${button.dataset.tipoAparato}, modelo ${button.dataset.modelo || 'sin modelo'} y ${button.dataset.kg || 'sin capacidad'} kg. Se prioriza el modelo, seguido del tema y la capacidad.`;
            results.innerHTML = '<p class="text-sm text-gray-600">Buscando videos relacionados...</p>';
            if (button.dataset.autoSelect !== 'true') {
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            try {
                const response = await fetch(`{{ route('videos.related-search') }}?${params}`, {
                    headers: { Accept: 'application/json' },
                });
                if (!response.ok) throw new Error('No se pudieron cargar los videos relacionados.');
                const modelGroups = await response.json();
                results.innerHTML = '';
                if (!modelGroups.length) {
                    results.innerHTML = '<p class="text-sm text-gray-600">No hay otros videos relacionados registrados.</p>';
                    return;
                }

                const card = document.createElement('article');
                card.className = 'col-span-full rounded-md border border-gray-200 bg-white p-4';
                card.innerHTML = '<div class="flex flex-wrap items-center justify-between gap-2"><div><h4 class="font-semibold text-gray-900">Enlaces relacionados</h4><p class="mt-1 text-xs text-gray-500">Títulos y enlaces priorizados por modelo, tema y capacidad.</p></div><button type="button" class="rounded-md bg-slate-800 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-900">Copiar toda la tarjeta</button></div><div class="mt-4 space-y-5"></div>';
                const groups = card.querySelector('div.mt-4');
                const copyLines = [];
                const seenLinks = new Set();
                const platformNames = { youtube: 'YouTube', facebook: 'Facebook', tiktok: 'TikTok' };

                modelGroups.forEach((modelGroup) => {
                    const modelSection = document.createElement('section');
                    modelSection.innerHTML = '<h5 class="border-b border-gray-200 pb-2 font-semibold text-gray-900"></h5><div class="mt-3 space-y-3"></div>';
                    modelSection.querySelector('h5').textContent = `Modelo: ${modelGroup.modelo}`;
                    const items = modelSection.querySelector('div');
                    const publications = modelGroup.videos.flatMap((video) => {
                        const entries = [];
                        if (video.link) {
                            entries.push({ platform: 'youtube', titulo: video.titulo, link: video.link });
                        }
                        if (video.linkLista) {
                            entries.push({ platform: 'youtube', titulo: video.titulo_lista || 'Lista de reproducción', link: video.linkLista });
                        }
                        video.social.forEach((publication) => {
                            if (publication.link) entries.push(publication);
                        });
                        return entries;
                    });

                    publications.forEach((publication) => {
                        if (seenLinks.has(publication.link)) return;
                        seenLinks.add(publication.link);

                        const label = platformNames[publication.platform] || publication.platform;
                        const item = document.createElement('div');
                        item.className = 'border-b border-gray-100 pb-3 last:border-0';
                        item.innerHTML = '<p class="text-xs font-semibold uppercase text-gray-500"></p><p class="mt-1 font-medium text-gray-900"></p><a class="mt-1 block break-all text-sm text-sky-700 underline" target="_blank" rel="noopener"></a>';
                        item.querySelector('p:first-child').textContent = label;
                        item.querySelector('p:nth-child(2)').textContent = publication.titulo;
                        const link = item.querySelector('a');
                        link.href = publication.link;
                        link.textContent = publication.link;
                        copyLines.push(`${label}: ${publication.titulo}\n${publication.link}`);
                        items.appendChild(item);
                    });

                    if (items.childElementCount) groups.appendChild(modelSection);
                });

                if (!copyLines.length) {
                    results.innerHTML = '<p class="text-sm text-gray-600">No hay enlaces disponibles en los videos relacionados.</p>';
                    return;
                }

                card.querySelector('button').addEventListener('click', async (event) => {
                    const copyButton = event.currentTarget;
                    await navigator.clipboard.writeText(copyLines.join('\n\n'));
                    copyButton.textContent = 'Copiado';
                    setTimeout(() => { copyButton.textContent = 'Copiar toda la tarjeta'; }, 1200);
                });
                results.appendChild(card);
            } catch (error) {
                results.innerHTML = '<p class="text-sm text-red-700">No se pudieron cargar los videos relacionados.</p>';
            }
        });
    });

    document.querySelector('.related-video-button[data-auto-select="true"]')?.click();
</script>
