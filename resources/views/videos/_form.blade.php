@php($ultimaListaTitulo = $ultimaListaTitulo ?? null)
@php($ultimaListaLink = $ultimaListaLink ?? null)
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
    <p class="mt-1 text-sm text-red-800">Indica si el video no se ha subido, es publico o esta privado. En privado solo se exige la fecha de subida; titulo, enlace y miniatura son opcionales para encontrarlos y completarlos despues.</p>
    @php($youtubeStatus = $value('youtube_publicado', $category === 'Naturaleza' ? 'no' : 'si'))
    <div class="mt-4"><label for="youtube_publicado" class="block text-sm font-medium text-gray-700">Estado del video en YouTube</label><select id="youtube_publicado" name="youtube_publicado" class="mt-1 block w-full rounded-md border-gray-300"><option value="no" @selected($youtubeStatus === 'no')>No se ha subido</option><option value="si" @selected($youtubeStatus === 'si')>Publicado (publico)</option><option value="privado" @selected($youtubeStatus === 'privado')>Subido como privado</option></select></div>
    <div id="youtube-details" class="mt-4 grid gap-5 md:grid-cols-2 {{ in_array($youtubeStatus, ['si', 'privado'], true) ? '' : 'hidden' }}">
        @if (! empty($ultimaListaLink))
            <div data-youtube-public-only class="md:col-span-2 rounded-md border border-blue-200 bg-blue-50 p-3">
                <label for="usar_ultima_lista" class="flex items-start gap-2 text-sm font-medium text-blue-950">
                    <input id="usar_ultima_lista" type="checkbox" class="mt-0.5 rounded border-blue-300 text-blue-700" @checked(old('usar_ultima_lista')) data-titulo-lista="{{ $ultimaListaTitulo }}" data-link-lista="{{ $ultimaListaLink }}">
                    <span>Usar la misma lista del último video registrado: <strong>{{ $ultimaListaTitulo ?: $ultimaListaLink }}</strong></span>
                </label>
                <p class="mt-1 pl-6 text-xs text-blue-800">Al marcar, se copiarán el título y el enlace de esa lista en este registro.</p>
            </div>
        @endif
        @foreach ([['fecha_subido','Fecha de subida/carga','date'],['fecha_publicacion','Fecha de publicacion','date'],['titulo','Titulo'],['youtube_link','Enlace de YouTube','url'],['titulo_lista','Titulo de la lista (opcional)'],['link_lista','Enlace de lista (opcional)','url']] as $field)
            @php($fieldValue = $value($field[0]))
            @php($fieldValue = $fieldValue instanceof \Carbon\CarbonInterface ? $fieldValue->format('Y-m-d') : $fieldValue)
            <div @if (!in_array($field[0], ['fecha_subido', 'titulo', 'youtube_link'], true)) data-youtube-public-only @endif @if (in_array($field[0], ['titulo', 'youtube_link'], true)) data-youtube-private-optional @endif>
                <label for="{{ $field[0] }}" class="block text-sm font-medium text-gray-700">{{ $field[1] }}</label>
                <input id="{{ $field[0] }}" name="{{ $field[0] }}" type="{{ $field[2] ?? 'text' }}" value="{{ $fieldValue }}" @required(($field[0] === 'fecha_subido' && in_array($youtubeStatus, ['si', 'privado'], true)) || (in_array($field[0], ['fecha_publicacion', 'titulo'], true) && $youtubeStatus === 'si')) data-youtube-required="{{ $field[0] === 'fecha_subido' ? 'upload' : (in_array($field[0], ['fecha_publicacion', 'titulo'], true) ? 'public' : 'optional') }}" class="mt-1 block w-full rounded-md border-gray-300">
                @if ($field[0] === 'fecha_subido')
                    <p class="mt-1 text-xs text-gray-500">Si la dejas vacia, se calculara automaticamente 5 dias antes de la fecha de publicacion.</p>
                @endif
                @if ($field[0] === 'youtube_link')
                    <p id="youtube-link-check" class="mt-1 hidden text-sm" role="status" aria-live="polite"></p>
                @endif
                <x-input-error :messages="$errors->get($field[0])" class="mt-1" />
            </div>
        @endforeach
        <div data-youtube-private-optional><label for="youtube_miniatura" class="block text-sm font-medium text-gray-700">Miniatura de YouTube (opcional)</label><input id="youtube_miniatura" name="youtube_miniatura" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm"><x-input-error :messages="$errors->get('youtube_miniatura')" class="mt-1" />@if ($video->youtube_miniatura)<img src="{{ asset('storage/'.$video->youtube_miniatura) }}" alt="Miniatura actual de YouTube" class="mt-2 h-20 w-32 rounded object-cover">@endif</div>
        <div data-youtube-public-only><label for="formato" class="block text-sm font-medium text-gray-700">Formato de video YouTube</label><select id="formato" name="formato" required class="mt-1 block w-full rounded-md border-gray-300"><option value="">Selecciona</option>@foreach (['Short', 'Video normal', 'Video normal corto', 'En vivo'] as $option)<option value="{{ $option }}" @selected($value('formato') === $option)>{{ $option }}</option>@endforeach</select><x-input-error :messages="$errors->get('formato')" class="mt-1" /></div>
        <div data-youtube-public-only><label for="youtube_formato_guardado" class="block text-sm font-medium text-gray-700">Version formateada para YouTube guardada</label><select id="youtube_formato_guardado" name="youtube_formato_guardado" required class="mt-1 block w-full rounded-md border-gray-300"><option value="">Selecciona</option><option value="si" @selected($value('youtube_formato_guardado') === 'si')>Si</option><option value="no" @selected($value('youtube_formato_guardado', 'no') === 'no')>No</option></select><x-input-error :messages="$errors->get('youtube_formato_guardado')" class="mt-1" /></div>
        <div data-youtube-public-only id="youtube-ruta-editado-field" class="{{ $value('youtube_formato_guardado') === 'si' ? '' : 'hidden' }}"><label for="youtube_ruta_editado" class="block text-sm font-medium text-gray-700">Ruta de la version final para YouTube</label><input id="youtube_ruta_editado" name="youtube_ruta_editado" value="{{ $value('youtube_ruta_editado') }}" class="mt-1 block w-full rounded-md border-gray-300"><x-input-error :messages="$errors->get('youtube_ruta_editado')" class="mt-1" /></div>
        <div data-youtube-public-only><label for="mes" class="block text-sm font-medium text-gray-700">Mes de publicacion (automatico)</label><input id="mes" name="mes" value="{{ $value('mes') }}" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100"><x-input-error :messages="$errors->get('mes')" class="mt-1" /></div>
        <div data-youtube-public-only><label for="anio" class="block text-sm font-medium text-gray-700">Año de publicacion (automatico)</label><input id="anio" name="anio" value="{{ $value('anio') }}" readonly class="mt-1 block w-full rounded-md border-gray-300"><x-input-error :messages="$errors->get('anio')" class="mt-1" /></div>
        <div data-youtube-public-only><label for="publicado_comunidad" class="block text-sm font-medium text-gray-700">Publicado en comunidad</label><select id="publicado_comunidad" name="publicado_comunidad" required class="mt-1 block w-full rounded-md border-gray-300"><option value="">Selecciona</option><option value="si" @selected($value('publicado_comunidad') === 'si')>Si</option><option value="no" @selected($value('publicado_comunidad', 'no') === 'no')>No</option></select></div>
        <div data-youtube-public-only class="md:col-span-2"><label for="linea_tiempo" class="block text-sm font-medium text-gray-700">Tarjetas de linea de tiempo</label><textarea id="linea_tiempo" name="linea_tiempo" rows="5" placeholder="00:00 Introduccion&#10;01:25 Diagnostico&#10;04:10 Solucion" class="mt-1 block w-full rounded-md border-gray-300">{{ $value('linea_tiempo') }}</textarea><x-input-error :messages="$errors->get('linea_tiempo')" class="mt-1" /></div>
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
        const monthNames = Array.from({ length: 12 }, (_, index) => (
            new Intl.DateTimeFormat('es', { month: 'long', timeZone: 'UTC' }).format(new Date(Date.UTC(2020, index, 1)))
        ));

        document.querySelectorAll('input[type="date"]').forEach((input) => {
            const dateParts = input.value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            const initialYear = dateParts ? Number(dateParts[1]) : new Date().getFullYear();
            const wrapper = document.createElement('div');
            wrapper.className = 'mt-1 grid grid-cols-3 gap-2';
            wrapper.setAttribute('role', 'group');
            const label = Array.from(document.querySelectorAll('label[for]')).find((item) => item.htmlFor === input.id);
            const fieldLabel = label?.textContent.trim() || 'Fecha';

            const createSelect = (part, label, placeholder) => {
                const select = document.createElement('select');
                select.id = `${input.id}_${part}`;
                select.className = 'block w-full rounded-md border-gray-300';
                select.setAttribute('aria-label', `${label} de ${fieldLabel}`);
                select.add(new Option(placeholder, ''));
                wrapper.append(select);
                return select;
            };

            const day = createSelect('day', 'Día', 'Día');
            const month = createSelect('month', 'Mes', 'Mes');
            const year = createSelect('year', 'Año', 'Año');

            monthNames.forEach((name, index) => month.add(new Option(name, String(index + 1).padStart(2, '0'))));

            const firstYear = 2020;
            const lastYear = 2027;
            for (let optionYear = firstYear; optionYear <= lastYear; optionYear += 1) {
                year.add(new Option(String(optionYear), String(optionYear)));
            }

            input.before(wrapper);
            input.classList.add('hidden');
            input.setAttribute('aria-hidden', 'true');

            if (label) label.htmlFor = day.id;

            const refreshDays = () => {
                const selectedDay = day.value;
                const daysInMonth = month.value && year.value
                    ? new Date(Date.UTC(Number(year.value), Number(month.value), 0)).getUTCDate()
                    : 31;

                day.replaceChildren(new Option('Día', ''));
                for (let optionDay = 1; optionDay <= daysInMonth; optionDay += 1) {
                    day.add(new Option(String(optionDay), String(optionDay).padStart(2, '0')));
                }
                day.value = Number(selectedDay) <= daysInMonth ? selectedDay : '';
            };

            const syncDate = () => {
                refreshDays();
                const isComplete = day.value && month.value && year.value;
                input.value = isComplete ? `${year.value}-${month.value}-${day.value}` : '';
                const isPartial = Boolean(day.value || month.value || year.value);
                [day, month, year].forEach((select) => {
                    select.required = Boolean(input._dateParts?.required || isPartial);
                });
                input.dispatchEvent(new Event('change', { bubbles: true }));
            };

            const setRequired = (required) => {
                input._dateParts.required = required;
                input.required = false;
                const isPartial = Boolean(day.value || month.value || year.value);
                [day, month, year].forEach((select) => {
                    select.required = required || isPartial;
                });
            };

            const setValue = (dateValue) => {
                const parts = dateValue.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!parts) return;
                year.value = parts[1];
                month.value = parts[2];
                refreshDays();
                day.value = parts[3];
                syncDate();
            };

            input._dateParts = { required: input.required, setRequired, setValue };
            input.required = false;

            if (dateParts) {
                year.value = dateParts[1];
                month.value = dateParts[2];
                day.value = dateParts[3];
                refreshDays();
            }

            [day, month, year].forEach((select) => select.addEventListener('change', syncDate));
        });

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
            const isPublic = !youtubePublished || youtubePublished.value === 'si';
            const isPrivate = youtubePublished?.value === 'privado';
            youtubeDetails?.classList.toggle('hidden', !isPublic && !isPrivate);
            youtubeDetails?.querySelectorAll('input, select, textarea, button').forEach((field) => {
                field.disabled = !isPublic && !isPrivate;
            });
            youtubeDetails?.querySelectorAll('[data-youtube-public-only]').forEach((section) => {
                section.classList.toggle('hidden', !isPublic);
                section.querySelectorAll('input, select, textarea, button').forEach((field) => {
                    field.disabled = !isPublic;
                });
            });
            youtubeDetails?.querySelectorAll('[data-youtube-private-optional]').forEach((section) => {
                section.classList.toggle('hidden', !isPublic && !isPrivate);
                section.querySelectorAll('input, select, textarea, button').forEach((field) => {
                    field.disabled = !isPublic && !isPrivate;
                });
            });
            youtubeDetails?.querySelectorAll('[data-youtube-required]').forEach((field) => {
                const required = field.dataset.youtubeRequired === 'upload'
                    ? isPublic || isPrivate
                    : field.dataset.youtubeRequired === 'public' && isPublic;
                if (field._dateParts) field._dateParts.setRequired(required);
                else field.required = required;
            });
            if (!isPublic && youtubeEditedPath) youtubeEditedPath.required = false;
        };

        youtubePublished?.addEventListener('change', toggleYoutubeDetails);
        toggleYoutubeDetails();

        const reuseLastPlaylist = document.getElementById('usar_ultima_lista');
        reuseLastPlaylist?.addEventListener('change', () => {
            if (!reuseLastPlaylist.checked) return;
            document.getElementById('titulo_lista').value = reuseLastPlaylist.dataset.tituloLista || '';
            document.getElementById('link_lista').value = reuseLastPlaylist.dataset.linkLista || '';
        });

        if (!publicationDate || !month || !year) return;

        const updatePeriod = () => {
            if (!publicationDate.value) return;
            const date = new Date(`${publicationDate.value}T00:00:00`);
            month.value = new Intl.DateTimeFormat('es', { month: 'long' }).format(date);
            year.value = String(date.getFullYear());
        };

        publicationDate.addEventListener('change', updatePeriod);
        updatePeriod();

        const uploadDate = document.getElementById('fecha_subido');
        let uploadDateWasAutoFilled = false;
        let updatingUploadDate = false;
        uploadDate?.addEventListener('change', () => {
            if (!updatingUploadDate) uploadDateWasAutoFilled = false;
        });

        const updateUploadDate = () => {
            if (!publicationDate.value || !uploadDate || (uploadDate.value && !uploadDateWasAutoFilled)) return;
            const date = new Date(`${publicationDate.value}T00:00:00Z`);
            date.setUTCDate(date.getUTCDate() - 5);
            const uploadDateValue = [
                date.getUTCFullYear(),
                String(date.getUTCMonth() + 1).padStart(2, '0'),
                String(date.getUTCDate()).padStart(2, '0'),
            ].join('-');

            updatingUploadDate = true;
            if (uploadDate._dateParts) uploadDate._dateParts.setValue(uploadDateValue);
            else {
                uploadDate.value = uploadDateValue;
                uploadDate.dispatchEvent(new Event('change', { bubbles: true }));
            }
            uploadDateWasAutoFilled = true;
            updatingUploadDate = false;
        };

        publicationDate.addEventListener('change', updateUploadDate);
        updateUploadDate();

        const customFields = [
            ['tipo_aparato', 'Otro', 'tipo_aparato_personalizado'],
            ['marca', 'Otra', 'marca_personalizada'],
            ['kg', 'personalizado', 'kg_personalizado'],
        ];

        const youtubeLink = document.getElementById('youtube_link');
        const youtubeLinkCheck = document.getElementById('youtube-link-check');
        let youtubeLinkCheckTimer;
        let youtubeLinkCheckController;

        const extractYoutubeVideoId = (value) => {
            try {
                const url = new URL(value);
                const host = url.hostname.toLowerCase();
                let videoId = '';

                if (host === 'youtu.be' || host === 'www.youtu.be') {
                    videoId = url.pathname.split('/').filter(Boolean)[0] ?? '';
                } else if (['youtube.com', 'www.youtube.com', 'm.youtube.com'].includes(host)) {
                    const segments = url.pathname.split('/').filter(Boolean);
                    if (segments[0] === 'watch') videoId = url.searchParams.get('v') ?? '';
                    else if (['shorts', 'embed', 'live'].includes(segments[0])) videoId = segments[1] ?? '';
                }

                return /^[A-Za-z0-9_-]{11}$/.test(videoId) ? videoId : null;
            } catch {
                return null;
            }
        };

        const checkYoutubeLink = async () => {
            if (!youtubeLink || !youtubeLinkCheck) return;
            const value = youtubeLink.value.trim();
            if (!extractYoutubeVideoId(value)) {
                youtubeLinkCheck.classList.add('hidden');
                youtubeLink.setCustomValidity('');
                return;
            }

            youtubeLinkCheckController?.abort();
            youtubeLinkCheckController = new AbortController();
            youtubeLinkCheck.textContent = 'Verificando si el video ya esta registrado...';
            youtubeLinkCheck.className = 'mt-1 text-sm text-gray-600';

            const params = new URLSearchParams({ url: value });
            const currentVideoId = @json($video->id);
            if (currentVideoId) params.set('video_id', currentVideoId);
            try {
                const response = await fetch(`{{ route('videos.check-youtube-link') }}?${params}`, {
                    headers: { Accept: 'application/json' },
                    signal: youtubeLinkCheckController.signal,
                });
                if (!response.ok) throw new Error('No se pudo verificar el enlace.');

                const result = await response.json();
                if (youtubeLink.value.trim() !== value) return;

                if (result.exists) {
                    youtubeLinkCheck.textContent = `Este video ya se encuentra registrado en el sistema${result.titulo ? ` como "${result.titulo}"` : ''}.`;
                    youtubeLinkCheck.className = 'mt-1 text-sm font-semibold text-red-700';
                    if (result.edit_url) {
                        const editLink = document.createElement('a');
                        editLink.href = result.edit_url;
                        editLink.textContent = 'Editar este video y agregar otra lista';
                        editLink.className = 'mt-1 block font-semibold text-sky-700 underline hover:text-sky-900';
                        editLink.setAttribute('aria-label', 'Editar el video ya registrado y agregar otra lista');
                        youtubeLinkCheck.append(editLink);
                    }
                    youtubeLink.setCustomValidity('Este video ya se encuentra registrado en el sistema.');
                } else {
                    youtubeLinkCheck.classList.add('hidden');
                    youtubeLink.setCustomValidity('');
                }
            } catch (error) {
                if (error.name === 'AbortError') return;
                youtubeLinkCheck.textContent = 'No se pudo verificar el enlace. Revisa la conexion e intenta nuevamente.';
                youtubeLinkCheck.className = 'mt-1 text-sm text-amber-700';
                youtubeLink.setCustomValidity('');
            }
        };

        youtubeLink?.addEventListener('input', () => {
            clearTimeout(youtubeLinkCheckTimer);
            youtubeLinkCheckController?.abort();
            youtubeLinkCheckTimer = setTimeout(checkYoutubeLink, 400);
        });
        if (youtubeLink?.value) checkYoutubeLink();

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