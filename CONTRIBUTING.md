# Guía de Contribución

¡Gracias por tu interés en contribuir a RegistroVideo! Esta guía te ayudará a empezar.

## 🚀 Cómo contribuir

### 1. Fork y clonar el proyecto

```bash
# Fork el repositorio en GitHub
# Luego clona tu fork
git clone https://github.com/tu-usuario/registrovideo.git
cd registrovideo
```

### 2. Crear una rama para tu feature

```bash
git checkout -b feature/nombre-descriptivo
# o para bugs:
git checkout -b fix/nombre-del-bug
```

### 3. Instalar dependencias

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### 4. Hacer tus cambios

Asegúrate de:
- ✅ Seguir el estilo de código existente
- ✅ Escribir tests para nueva funcionalidad
- ✅ Actualizar documentación si es necesario
- ✅ Probar tus cambios localmente

### 5. Ejecutar tests

```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test --filter=NombreDelTest

# Con cobertura
php artisan test --coverage
```

### 6. Commit y push

```bash
git add .
git commit -m "tipo: descripción clara del cambio"
git push origin feature/nombre-descriptivo
```

### 7. Crear Pull Request

1. Ve a tu fork en GitHub
2. Haz clic en "Compare & pull request"
3. Describe tus cambios claramente
4. Espera la revisión

## 📝 Convenciones de commits

Usa prefijos claros en tus mensajes de commit:

- `feat:` Nueva funcionalidad
- `fix:` Corrección de bug
- `refactor:` Refactorización de código
- `perf:` Mejora de rendimiento
- `style:` Cambios de formato (sin cambios de código)
- `test:` Agregar o modificar tests
- `docs:` Cambios en documentación
- `chore:` Tareas de mantenimiento

**Ejemplos:**
```bash
feat: agregar exportación de videos a JSON
fix: corregir conteo de publicaciones en Facebook
refactor: simplificar lógica de validación en VideoStoreRequest
perf: optimizar consulta de búsqueda con índices
test: agregar prueba para limpieza de miniaturas
docs: actualizar README con nuevas instrucciones
```

## 🎨 Estilo de código

### PHP (Laravel)

- Seguir [PSR-12](https://www.php-fig.org/psr/psr-12/)
- Usar type hints en todos los métodos
- Documentar métodos complejos con PHPDoc
- Nombres descriptivos en español para variables de usuario

**Ejemplo:**
```php
public function store(VideoStoreRequest $request): RedirectResponse
{
    $data = $request->validated();
    $video = $request->user()->videos()->create($data);
    
    return to_route('dashboard')->with('status', 'Video guardado.');
}
```

### Blade Templates

- Usar directivas de Blade (`@if`, `@foreach`, etc.)
- Escapar variables con `{{ }}` (nunca `{!! !!}` con input de usuario)
- Tailwind CSS para estilos

**Ejemplo:**
```blade
@if ($videos->isEmpty())
    <p class="text-gray-500">No hay videos registrados.</p>
@else
    @foreach ($videos as $video)
        <div>{{ $video->titulo }}</div>
    @endforeach
@endif
```

### JavaScript

- ES6+ moderno
- `async/await` para operaciones asíncronas
- Comentarios en español

**Ejemplo:**
```javascript
async function buscarVideos(query) {
    const response = await fetch(`/videos/related-search?q=${query}`);
    const videos = await response.json();
    return videos;
}
```

## 🧪 Testing

### Crear un nuevo test

```bash
php artisan make:test NombreDelTest
```

### Estructura de test

```php
public function test_descripcion_clara_de_lo_que_prueba(): void
{
    // Arrange (preparar)
    $user = User::factory()->create();
    
    // Act (actuar)
    $response = $this->actingAs($user)->post(route('videos.store'), [
        'titulo' => 'Test video',
        // ...más datos
    ]);
    
    // Assert (verificar)
    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('youtube_json_trackers', ['titulo' => 'Test video']);
}
```

### Coverage mínimo

- Nuevas features: 80%+ de cobertura
- Métodos críticos: 100% de cobertura

## 🐛 Reportar bugs

Al reportar un bug, incluye:

1. **Descripción clara**: ¿Qué debería pasar vs qué pasa?
2. **Pasos para reproducir**:
   ```
   1. Ve a la página X
   2. Haz clic en Y
   3. Observa el error Z
   ```
3. **Entorno**:
   - Versión de PHP
   - Versión de Laravel
   - Sistema operativo
4. **Logs relevantes** (si aplica)

## 💡 Proponer mejoras

Para proponer nuevas funcionalidades:

1. Abre un Issue explicando:
   - ¿Qué problema resuelve?
   - ¿Cómo funcionaría?
   - ¿Hay alternativas?
2. Espera feedback antes de implementar
3. Si es aprobado, crea un PR

## 🔍 Revisión de código

Los PRs serán revisados considerando:

- ✅ Tests incluidos y pasando
- ✅ Código limpio y legible
- ✅ Sin regresiones (no rompe funcionalidad existente)
- ✅ Documentación actualizada
- ✅ Sin warnings de seguridad

## 📚 Recursos útiles

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [PHP The Right Way](https://phptherightway.com/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)

## ❓ Preguntas

Si tienes dudas:
1. Revisa la documentación existente
2. Busca en Issues cerrados
3. Abre un nuevo Issue con tu pregunta

## 📄 Licencia

Al contribuir, aceptas que tus contribuciones se licencien bajo la misma licencia del proyecto (MIT).

---

¡Gracias por hacer de RegistroVideo un mejor proyecto! 🎉
