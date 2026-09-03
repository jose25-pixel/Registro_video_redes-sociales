@php($value = fn (string $field, mixed $default = '') => old($field, $video->{$field} ?? $default))
@if ($errors->any())<div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700"><p class="font-semibold">Revisa los campos marcados.</p><ul class="mt-2 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<h3 class="mb-4 border-l-4 border-gray-700 pl-3 text-lg font-semibold text-gray-900">Datos del aparato y del error</h3>
<div class="grid gap-5 md:grid-cols-2">
    <input type="hidden" name="categoria" value="{{ $value('categoria', auth()->user()->categoria_principal) }}">
    @php($category = auth()->user()->categoria_principal ?? $value('categoria', 'Naturaleza'))
    @php($subcategories = ['Naturaleza' => ['Plantas', 'Animales', 'Flores', 'Otro'], 'Linea blanca' => ['Lavadoras', 'Neveras', 'Secadoras', 'Aire acondicionado', 'Otro'], 'Tecnologia' => ['Celulares', 'Computadoras', 'Televisores', 'Otro'], 'Cocina' => ['Recetas', 'Bebidas', 'Panaderia', 'Otro'], 'Educacion' => ['Tutoriales', 'Cursos', 'Consejos', 'Otro'], 'Otro' => ['General', 'Reseñas', 'Tutoriales', 'Otro']][$category] ?? ['General', 'Otro'])
    @if ($category === 'Naturaleza')
        <div><label for="subcategoria" class="block text-sm font-medium text-gray-700">Subcategoria</label><select id="subcategoria" name="subcategoria" required class="mt-1 block w-full rounded-md border-gray-300"><option value="">Selecciona</option>@foreach ($subcategories as $option)<option value="{{ $option }}" @selected($value('subcategoria') === $option)>{{ $option }}</option>@endforeach</select><x-input-error :messages="$errors->get('subcategoria')" class="mt-1" /></div>
        <div><label for="ubicacion" class="block text-sm font-medium text-gray-700">Ubicacion (opcional)</label><input id="ubicacion" name="ubicacion" value="{{ $value('ubicacion') }}" class="mt-1 block w-full rounded-md border-gray-300"></div>
        <div><label for="especie" class="block text-sm font-medium text-gray-700">Especie o tipo (opcional)</label><input id="especie" name="especie" value="{{ $value('especie') }}" class="mt-1 block w-full rounded-md border-gray-300"></div>
    @else
        <input type="hidden" name="subcategoria" value="{{ $value('subcategoria', $category) }}">
    @endif
    @if ($category !== 'Naturaleza')
    @php($knownTypes = ['Lavadora', 'Nevera', 'Secadora', 'Nevera frio seco', 'Frigobar', 'Camara', 'Nevera pequeña', 'Aire acondicionado', 'Microondas'])
    @php($knownBrands = ['Samsung', 'LG', 'GE', 'Oster', 'Whirlpool', 'Mabe', 'Electrolux', 'Frigidaire', 'Daewoo', 'Hisense', 'Midea', 'TCL', 'Panasonic', 'Haceb', 'Indurama', 'Nedoca', 'Cetron', 'Black+Decker', 'Philips', 'Hamilton Beach'])
    @php($currentType = $value('tipo_aparato'))
    @php($currentBrand = $value('marca'))
    @php($typeIsCustom = $currentType !== '' && !in_array($currentType, $knownTypes, true))
    @php($brandIsCustom = $currentBrand !== '' && !in_array($currentBrand, $knownBrands, true))
    @php($currentKg = (string) $value('kg'))
    <div><label for="tipo_aparato" class="block text-sm font-medium text-gray-700">Tipo de aparato</label><select id="tipo_aparato" name="tipo_aparato" required class="mt-1 block w-full rounded-md border-gray-300"><option value="">Selecciona</option>@foreach ($knownTypes as $option)<option value="{{ $option }}" @selected($currentType === $option)>{{ $option }}</option>@endforeach<option value="Otro" @selected($typeIsCustom)>Otro</option></select><input id="tipo_aparato_personalizado" name="tipo_aparato_personalizado" value="{{ old('tipo_aparato_personalizado', $typeIsCustom ? $currentType : '') }}" placeholder="Escribe otro tipo" class="mt-2 hidden w-full rounded-md border-gray-300"><x-input-error :messages="$errors->get('tipo_aparato_personalizado')" class="mt-1" /></div>
    <div><label for="marca" class="block text-sm font-medium text-gray-700">Marca</label><select id="marca" name="marca" required class="mt-1 block w-full rounded-md border-gray-300"><option value="">Selecciona</option>@foreach ($knownBrands as $option)<option value="{{ $option }}" @selected($currentBrand === $option)>{{ $option }}</option>@endforeach<option value="Otra" @selected($brandIsCustom)>Otra</option></select><input id="marca_personalizada" name="marca_personalizada" value="{{ old('marca_personalizada', $brandIsCustom ? $currentBrand : '') }}" placeholder="Escribe otra marca" class="mt-2 hidden w-full rounded-md border-gray-300"><x-input-error :messages="$errors->get('marca_personalizada')" class="mt-1" /></div>
    <div id="kg-field"><label for="kg" class="block text-sm font-medium text-gray-700">Capacidad de carga (kg)</label><select id="kg" name="kg" class="mt-1 block w-full rounded-md border-gray-300"><option value="">Selecciona</option>@foreach (range(10, 25) as $option)<option value="{{ $option }}" @selected($currentKg === (string) $option)>{{ $option }} kg</option>@endforeach<option value="personalizado" @selected($currentKg !== '' && !in_array($currentKg, array_map('strval', range(10, 25)), true))>Personalizado</option></select><input id="kg_personalizado" name="kg_personalizado" type="number" min="0.1" step="0.1" value="{{ old('kg_personalizado', $currentKg !== '' && !in_array($currentKg, array_map('strval', range(10, 25)), true) ? $currentKg : '') }}" placeholder="Escribe los KG" class="mt-2 hidden w-full rounded-md border-gray-300"><x-input-error :messages="$errors->get('kg')" class="mt-1" /><x-input-error :messages="$errors->get('kg_personalizado')" class="mt-1" /></div>
    @foreach ([['modelo_comercial','Modelo comercial'],['modelo','Modelo'],['tecnologia','Tecnologia'],['tema_error','Tema del error']] as $field)
        <div><label for="{{ $field[0] }}" class="block text-sm font-medium text-gray-700">{{ $field[1] }}</label><input id="{{ $field[0] }}" name="{{ $field[0] }}" type="{{ $field[2] ?? 'text' }}" value="{{ $value($field[0]) }}" @required($field[0] !== 'link_lista') class="mt-1 block w-full rounded-md border-gray-300"><x-input-error :messages="$errors->get($field[0])" class="mt-1" /></div>
    @endforeach
    @endif
    <div><label for="guardado_original" class="block text-sm font-medium text-gray-700">Guardado original</label><select id="guardado_original" name="guardado_original" required class="mt-1 block w-full rounded-md border-gray-300"><option value="">Selecciona</option>@foreach (['Si', 'No', 'Verificar'] as $option)<option value="{{ $option }}" @selected($value('guardado_original', 'No') === $option)>{{ $option }}</option>@endforeach</select><x-input-error :messages="$errors->get('guardado_original')" class="mt-1" /></div>
    <div id="ruta-original-field" class="{{ $value('guardado_original') === 'Si' ? '' : 'hidden' }}"><label for="ruta_ubicacion_original" class="block text-sm font-medium text-gray-700">Ruta del video original</label><input id="ruta_ubicacion_original" name="ruta_ubicacion_original" value="{{ $value('ruta_ubicacion_original') }}" class="mt-1 block w-full rounded-md border-gray-300"><x-input-error :messages="$errors->get('ruta_ubicacion_original')" class="mt-1" /></div>
    <div><label for="guardado_editado" class="block text-sm font-medium text-gray-700">Video unido/editado guardado</label><select id="guardado_editado" name="guardado_editado" required class="mt-1 block w-full rounded-md border-gray-300"><option value="">Selecciona</option>@foreach (['Si', 'No', 'Posible'] as $option)<option value="{{ $option }}" @selected($value('guardado_editado', 'No') === $option)>{{ $option }}</option>@endforeach</select><x-input-error :messages="$errors->get('guardado_editado')" class="mt-1" /></div>
    <div id="ruta-editado-field" class="{{ $value('guardado_editado') === 'Si' ? '' : 'hidden' }}"><label for="ruta_ubicacion_editado" class="block text-sm font-medium text-gray-700">Ruta del video unido/editado</label><input id="ruta_ubicacion_editado" name="ruta_ubicacion_editado" value="{{ $value('ruta_ubicacion_editado') }}" class="mt-1 block w-full rounded-md border-gray-300"><x-input-error :messages="$errors->get('ruta_ubicacion_editado')" class="mt-1" /></div>
</div>

<div class="mt-8 rounded-lg border border-red-200 bg-red-50 p-5">
    <h3 class="border-l-4 border-red-600 pl-3 text-lg font-semibold text-red-950">YouTube</h3>
    <p class="mt-1 text-sm text-red-800">Datos de la version preparada y publicada en YouTube.</p>
    @if ($category === 'Naturaleza')
        <div class="mt-4"><label for="youtube_publicado" class="block text-sm font-medium text-gray-700">¿Se subio una version a YouTube?</label><select id="youtube_publicado" name="youtube_publicado" class="mt-1 block w-full rounded-md border-gray-300"><option value="no" @selected($value('youtube_publicado', 'no') === 'no')>No</option><option value="si" @selected($value('youtube_publicado') === 'si')>Si</option></select></div>
    @else
        <input type="hidden" name="youtube_publicado" value="si">
    @endif
    <div id="youtube-details" class="mt-4 grid gap-5 md:grid-cols-2 {{ $category === 'Naturaleza' && $value('youtube_publicado', 'no') !== 'si' ? 'hidden' : '' }}">
        @foreach ([['fecha_subido','Fecha de subida/carga','date'],['fecha_publicacion','Fecha de publicacion','date'],['titulo','Titulo'],['youtube_link','Enlace de YouTube','url'],['titulo_lista','Titulo de la lista (opcional)'],['link_lista','Enlace de lista (opcional)','url']] as $field)
            @php($fieldValue = $value($field[0]))
            @php($fieldValue = $fieldValue instanceof \Carbon\CarbonInterface ? $fieldValue->format('Y-m-d') : $fieldValue)
            <div><label for="{{ $field[0] }}" class="block text-sm font-medium text-gray-700">{{ $field[1] }}</label><input id="{{ $field[0] }}" name="{{ $field[0] }}" type="{{ $field[2] ?? 'text' }}" value="{{ $fieldValue }}" @required(!in_array($field[0], ['youtube_link', 'link_lista', 'titulo_lista', 'youtube_ruta_editado'])) data-youtube-required="{{ in_array($field[0], ['fecha_subido', 'fecha_publicacion', 'titulo'], true) ? 'true' : 'false' }}" class="mt-1 block w-full rounded-md border-gray-300">@if (false)<div class="mt-2"><label for="related_video" class="block text-xs font-medium text-gray-600">Videos relacionados registrados</label><select id="related_video" class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="">Escribe modelo, marca o tipo para buscar</option></select><p id="related_video_status" class="mt-1 text-xs text-gray-500"></p></div>@endif<x-input-error :messages="$errors->get($field[0])" class="mt-1" /></div>
        @endforeach
        <div><label for="youtube_miniatura" class="block text-sm font-medium text-gray-700">Miniatura de YouTube</label><input id="youtube_miniatura" name="youtube_miniatura" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm"><x-input-error :messages="$errors->get('youtube_miniatura')" class="mt-1" />@if ($video->youtube_miniatura)<img src="{{ asset('storage/'.$video->youtube_miniatura) }}" alt="Miniatura actual de YouTube" class="mt-2 h-20 w-32 rounded object-cover">@endif</div>
        <div><label for="formato" class="block text-sm font-medium text-gray-700">Formato de video YouTube</label><select id="formato" name="formato" required data-youtube-required="true" class="mt-1 block w-full rounded-md border-gray-300"><option value="">Selecciona</option>@foreach (['Short', 'Video normal', 'Video normal corto', 'En vivo'] as $option)<option value="{{ $option }}" @selected($value('formato') === $option)>{{ $option }}</option>@endforeach</select><x-input-error :messages="$errors->get('formato')" class="mt-1" /></div>
        <div><label for="youtube_formato_guardado" class="block text-sm font-medium text-gray-700">Version formateada para YouTube guardada</label><select id="youtube_formato_guardado" name="youtube_formato_guardado" required class="mt-1 block w-full rounded-md border-gray-300"><option value="">Selecciona</option><option value="si" @selected($value('youtube_formato_guardado') === 'si')>Si</option><option value="no" @selected($value('youtube_formato_guardado', 'no') === 'no')>No</option></select><x-input-error :messages="$errors->get('youtube_formato_guardado')" class="mt-1" /></div>
        <div id="youtube-ruta-editado-field" class="{{ $value('youtube_formato_guardado') === 'si' ? '' : 'hidden' }}"><label for="youtube_ruta_editado" class="block text-sm font-medium text-gray-700">Ruta de la version final para YouTube</label><input id="youtube_ruta_editado" name="youtube_ruta_editado" value="{{ $value('youtube_ruta_editado') }}" class="mt-1 block w-full rounded-md border-gray-300"><x-input-error :messages="$errors->get('youtube_ruta_editado')" class="mt-1" /></div>
        <div><label for="mes" class="block text-sm font-medium text-gray-700">Mes de publicacion (automatico)</label><input id="mes" name="mes" value="{{ $value('mes') }}" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100"><x-input-error :messages="$errors->get('mes')" class="mt-1" /></div>
        <div><label for="anio" class="block text-sm font-medium text-gray-700">Año de publicacion (automatico)</label><input id="anio" name="anio" value="{{ $value('anio') }}" readonly class="mt-1 block w-full rounded-md border-gray-300"><x-input-error :messages="$errors->get('anio')" class="mt-1" /></div>
        <div><label for="publicado_comunidad" class="block text-sm font-medium text-gray-700">Publicado en comunidad</label><select id="publicado_comunidad" name="publicado_comunidad" required class="mt-1 block w-full rounded-md border-gray-300"><option value="">Selecciona</option><option value="si" @selected($value('publicado_comunidad') === 'si')>Si</option><option value="no" @selected($value('publicado_comunidad', 'no') === 'no')>No</option></select></div>
        <div class="md:col-span-2"><label for="linea_tiempo" class="block text-sm font-medium text-gray-700">Tarjetas de linea de tiempo</label><textarea id="linea_tiempo" name="linea_tiempo" rows="5" placeholder="00:00 Introduccion&#10;01:25 Diagnostico&#10;04:10 Solucion" class="mt-1 block w-full rounded-md border-gray-300">{{ $value('linea_tiempo') }}</textarea><x-input-error :messages="$errors->get('linea_tiempo')" class="mt-1" /></div>
    </div>
    <div class="mt-5"><label for="descripcion" class="block text-sm font-medium text-gray-700">Descripcion</label><textarea id="descripcion" name="descripcion" rows="4" required class="mt-1 block w-full rounded-md border-gray-300">{{ $value('descripcion') }}</textarea><x-input-error :messages="$errors->get('descripcion')" class="mt-1" /></div>
</div>

<div class="mt-8 border-t border-gray-200 pt-6">
    <h3 class="text-lg font-semibold text-gray-900">Versiones para otras redes <span class="font-normal text-sm text-gray-500">(opcional)</span></h3>
    <div class="mt-4 grid gap-5 md:grid-cols-2">
        @foreach (['facebook' => 'Facebook', 'tiktok' => 'TikTok', 'instagram' => 'Instagram'] as $platform => $label)
            @php($social = $video->socialPublications->firstWhere('platform', $platform))
            @php($socialValue = fn (string $field, mixed $default = '') => old("social.{$platform}.{$field}", optional($video->socialPublications->firstWhere('platform', $platform))->{$field} ?? $default))
            @php($platformClasses = $platform === 'facebook' ? ['border-blue-200 bg-blue-50', 'border-blue-600 text-blue-950'] : ['border-purple-200 bg-purple-50', 'border-purple-600 text-purple-950'])
            <section class="social-platform rounded-lg border {{ $platformClasses[0] }} p-4" data-platform="{{ $platform }}">
                <h4 class="border-l-4 {{ $platformClasses[1] }} pl-3 font-semibold">{{ $label }}</h4>
                <div class="mt-3 grid gap-4">
                    <div><label for="{{ $platform }}_publicado" class="block text-sm font-medium text-gray-700">¿Se subio una version a {{ $label }}?</label><select id="{{ $platform }}_publicado" name="social[{{ $platform }}][publicado]" class="mt-1 block w-full rounded-md border-gray-300"><option value="no" @selected($socialValue('publicado', 'no') === 'no')>No</option><option value="si" @selected($socialValue('publicado') === 'si')>Si</option></select></div>
                    <div class="social-details hidden grid gap-4">
                    <div><label for="{{ $platform }}_titulo" class="block text-sm font-medium text-gray-700">Titulo del video {{ $label }}</label><input id="{{ $platform }}_titulo" name="social[{{ $platform }}][titulo]" value="{{ $socialValue('titulo') }}" class="mt-1 block w-full rounded-md border-gray-300"></div>
                    <div><label for="{{ $platform }}_tipo" class="block text-sm font-medium text-gray-700">Formato de video {{ $label }}</label><select id="{{ $platform }}_tipo" name="social[{{ $platform }}][tipo]" class="mt-1 block w-full rounded-md border-gray-300"><option value="">Seleccione</option>@foreach (($platform === 'facebook' ? ['Reel', 'Video normal', 'Video normal corto'] : ['Short', 'Video normal', 'Video normal corto', 'En vivo']) as $option)<option value="{{ $option }}" @selected($socialValue('tipo') === $option)>{{ $option }}</option>@endforeach</select></div>
                    @php($socialDate = $socialValue('fecha'))
                    <div><label for="{{ $platform }}_fecha" class="block text-sm font-medium text-gray-700">Fecha de publicacion {{ $label }}</label><input id="{{ $platform }}_fecha" name="social[{{ $platform }}][fecha]" type="date" value="{{ $socialDate instanceof \Carbon\CarbonInterface ? $socialDate->format('Y-m-d') : $socialDate }}" class="mt-1 block w-full rounded-md border-gray-300"></div>
                    <div><label for="{{ $platform }}_miniatura" class="block text-sm font-medium text-gray-700">Miniatura de {{ $label }} <span class="font-normal text-gray-500">(opcional)</span></label><input id="{{ $platform }}_miniatura" name="social[{{ $platform }}][miniatura]" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm">@if ($social?->miniatura)<img src="{{ asset('storage/'.$social->miniatura) }}" alt="Miniatura actual de {{ $label }}" class="mt-2 h-20 w-32 rounded object-cover">@endif</div>
                    <div><label for="{{ $platform }}_link" class="block text-sm font-medium text-gray-700">Link {{ $label }}</label><input id="{{ $platform }}_link" name="social[{{ $platform }}][link]" type="url" value="{{ $socialValue('link') }}" placeholder="https://www.{{ strtolower($label) }}.com/..." class="mt-1 block w-full rounded-md border-gray-300"></div>
                    <div><label for="{{ $platform }}_formato_guardado" class="block text-sm font-medium text-gray-700">Version formateada para {{ $label }} guardada</label><select id="{{ $platform }}_formato_guardado" name="social[{{ $platform }}][formato_guardado]" class="mt-1 block w-full rounded-md border-gray-300"><option value="no" @selected($socialValue('formato_guardado', 'no') === 'no')>No</option><option value="si" @selected($socialValue('formato_guardado') === 'si')>Si</option></select></div>
                    </div>
                </div>
            </section>
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const publicationDate = document.getElementById('fecha_publicacion');
        const month = document.getElementById('mes');
        const year = document.getElementById('anio');
        const youtubeEditedPath = document.getElementById('youtube_ruta_editado');
        const youtubeEditedPathField = document.getElementById('youtube-ruta-editado-field');
        const youtubeSavedFormat = document.getElementById('youtube_formato_guardado');
        const youtubePublished = document.getElementById('youtube_publicado');
        const youtubeDetails = document.getElementById('youtube-details');
        const originalVideoSaved = document.getElementById('guardado_original');
        const originalVideoPathField = document.getElementById('ruta-original-field');
        const originalVideoPath = document.getElementById('ruta_ubicacion_original');
        const editedVideoSaved = document.getElementById('guardado_editado');
        const editedVideoPathField = document.getElementById('ruta-editado-field');
        const editedVideoPath = document.getElementById('ruta_ubicacion_editado');

        const toggleVideoPath = (select, field, input, savedValue) => {
            const isSaved = select?.value === savedValue;
            field?.classList.toggle('hidden', !isSaved);
            if (input) input.required = isSaved;
        };

        const toggleOriginalVideoPath = () => toggleVideoPath(originalVideoSaved, originalVideoPathField, originalVideoPath, 'Si');
        const toggleEditedVideoPath = () => toggleVideoPath(editedVideoSaved, editedVideoPathField, editedVideoPath, 'Si');

        originalVideoSaved?.addEventListener('change', toggleOriginalVideoPath);
        editedVideoSaved?.addEventListener('change', toggleEditedVideoPath);
        toggleOriginalVideoPath();
        toggleEditedVideoPath();

        const toggleYoutubeEditedPath = () => {
            const isSaved = youtubeSavedFormat?.value === 'si';
            youtubeEditedPathField?.classList.toggle('hidden', !isSaved);
            if (youtubeEditedPath) youtubeEditedPath.required = isSaved;
        };

        youtubeSavedFormat?.addEventListener('change', toggleYoutubeEditedPath);
        toggleYoutubeEditedPath();

        const toggleYoutubeDetails = () => {
            const isPublished = !youtubePublished || youtubePublished.value === 'si';
            youtubeDetails?.classList.toggle('hidden', !isPublished);
            youtubeDetails?.querySelectorAll('[data-youtube-required="true"]').forEach((field) => { field.required = isPublished; });
            if (!isPublished && youtubeEditedPath) youtubeEditedPath.required = false;
        };

        youtubePublished?.addEventListener('change', toggleYoutubeDetails);
        toggleYoutubeDetails();

        if (!publicationDate || !month || !year) return;

        const updatePeriod = () => {
            if (!publicationDate.value) return;
            const date = new Date(`${publicationDate.value}T00:00:00`);
            month.value = new Intl.DateTimeFormat('es', { month: 'long' }).format(date);
            year.value = String(date.getFullYear());
        };

        publicationDate.addEventListener('change', updatePeriod);
        updatePeriod();

        const customFields = [
            ['tipo_aparato', 'Otro', 'tipo_aparato_personalizado'],
            ['marca', 'Otra', 'marca_personalizada'],
            ['kg', 'personalizado', 'kg_personalizado'],
        ];

        const relatedModel = document.getElementById('modelo');
        const relatedType = document.getElementById('tipo_aparato');
        const relatedBrand = document.getElementById('marca');
        const relatedVideo = document.getElementById('related_video');
        const relatedStatus = document.getElementById('related_video_status');
        const youtubeLink = document.getElementById('youtube_link');
        let relatedSearchTimer;

        const loadRelatedVideos = () => {
            const params = new URLSearchParams({
                modelo: relatedModel?.value ?? '',
                tipo_aparato: relatedType?.value ?? '',
                marca: relatedBrand?.value ?? '',
            });
            if (!relatedVideo) return;
            relatedVideo.innerHTML = '<option value="">Buscando videos relacionados...</option>';
            fetch(`{{ route('videos.related-search') }}?${params}`)
                .then((response) => response.json())
                .then((videos) => {
                    relatedVideo.innerHTML = '<option value="">Selecciona un video para cargar su enlace</option>';
                    videos.forEach((video) => {
                        const option = document.createElement('option');
                        option.value = video.link;
                        option.textContent = video.label;
                        relatedVideo.appendChild(option);
                    });
                    relatedStatus.textContent = videos.length ? `${videos.length} video(s) relacionado(s)` : 'No hay videos relacionados registrados';
                })
                .catch(() => {
                    relatedVideo.innerHTML = '<option value="">No se pudo buscar</option>';
                    relatedStatus.textContent = 'Revisa la conexion e intenta nuevamente';
                });
        };

        [relatedModel, relatedType, relatedBrand].forEach((field) => field?.addEventListener('change', () => {
            clearTimeout(relatedSearchTimer);
            relatedSearchTimer = setTimeout(loadRelatedVideos, 250);
        }));
        relatedVideo?.addEventListener('change', () => {
            if (relatedVideo.value && youtubeLink) youtubeLink.value = relatedVideo.value;
        });

        customFields.forEach(([selectId, customValue, inputId]) => {
            const select = document.getElementById(selectId);
            const input = document.getElementById(inputId);
            if (!select || !input) return;

            const toggleCustomField = () => {
                const visible = select.value === customValue;
                input.classList.toggle('hidden', !visible);
                input.required = visible;
            };

            select.addEventListener('change', toggleCustomField);
            toggleCustomField();
        });

        const applianceType = document.getElementById('tipo_aparato');
        const kgField = document.getElementById('kg-field');
        const kgSelect = document.getElementById('kg');
        if (applianceType && kgField && kgSelect) {
            const toggleKgField = () => {
                const isWashingMachine = applianceType.value === 'Lavadora';
                kgField.classList.toggle('hidden', !isWashingMachine);
                kgSelect.required = isWashingMachine;
            };

            applianceType.addEventListener('change', toggleKgField);
            toggleKgField();
        }

        document.querySelectorAll('.social-platform').forEach((platform) => {
            const select = platform.querySelector('select[name$="[publicado]"]');
            const details = platform.querySelector('.social-details');
            if (!select || !details) return;

            const toggleSocialDetails = () => details.classList.toggle('hidden', select.value !== 'si');
            select.addEventListener('change', toggleSocialDetails);
            toggleSocialDetails();
        });
    });
</script>