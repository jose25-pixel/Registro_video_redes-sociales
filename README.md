# 📹 RegistroVideo

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=flat&logo=mysql)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.0-06B6D4?style=flat&logo=tailwind-css)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/Tests-28%20passed-success)](tests)

Sistema de gestión y registro de videos de reparación para YouTube, Facebook, TikTok e Instagram. Organiza tus contenidos, referencias y enlaces de forma centralizada con búsqueda avanzada y estadísticas por periodo.

## 🚀 Características

- ✅ **Autenticación segura** con Laravel Breeze
- 📊 **Dashboard con estadísticas** por periodo (último mes, trimestre, año, personalizado)
- 🔍 **Búsqueda avanzada** por título, modelo, marca, error
- 📱 **Multi-plataforma**: gestión de YouTube, Facebook, TikTok e Instagram
- 🖼️ **Miniaturas**: soporte para imágenes de previsualización
- 📋 **Referencias relacionadas**: encuentra videos similares por aparato/modelo
- 🔒 **Aislamiento de datos**: cada usuario solo ve sus propios registros
- 🧪 **Tests automatizados**: 28 pruebas con 85 aserciones
- ⚡ **Rate limiting**: protección contra ataques de fuerza bruta

## 📋 Requisitos

- PHP 8.2 o superior
- MySQL 8.0+
- Composer
- Node.js 18+ y npm
- Extensiones PHP: `mbstring`, `pdo_mysql`, `openssl`, `fileinfo`, `gd`

## 🛠️ Instalación

### Opción 1: Instalación automática (recomendada)

**Linux/Mac:**
```bash
chmod +x install.sh
./install.sh
```

**Windows (PowerShell como administrador):**
```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force
.\install.ps1
```

El script automáticamente:
- ✅ Verifica requisitos
- ✅ Instala dependencias (Composer + npm)
- ✅ Copia y configura `.env`
- ✅ Genera clave de aplicación
- ✅ Configura credenciales de base de datos
- ✅ Ejecuta migraciones (opcional)
- ✅ Crea enlace simbólico de storage
- ✅ Compila assets

### Opción 2: Instalación manual

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/registrovideo.git
cd registrovideo
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

Edita `.env` y configura tu base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=registrovideo
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

### 4. Crear base de datos y ejecutar migraciones

```bash
# Crear la base de datos manualmente o con:
mysql -u root -p -e "CREATE DATABASE registrovideo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Ejecutar migraciones
php artisan migrate
```

### 5. Crear enlace simbólico para storage

```bash
php artisan storage:link
```

### 6. Compilar assets

```bash
npm run dev   # Para desarrollo
npm run build # Para producción
```

## 🏃 Ejecutar el proyecto

### Desarrollo local

**Opción 1: Servidor integrado de Laravel**

```bash
php artisan serve
```

Abre http://localhost:8000

**Opción 2: Con npm (incluye hot reload de Vite)**

Terminal 1:
```bash
php artisan serve
```

Terminal 2:
```bash
npm run dev
```

### Con Laragon (Windows)

1. Coloca el proyecto en `C:\laragon\www\registrovideo`
2. Asegúrate de que MySQL esté corriendo
3. Accede a http://registrovideo.test

## 🧪 Ejecutar tests

```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test --filter=YoutubeJsonTrackerTest

# Con cobertura
php artisan test --coverage
```

**Nota**: Los tests usan la base de datos `registrovideo_testing` (configurada en `phpunit.xml`).

### Verificación pre-commit

Antes de hacer commit, ejecuta:

```bash
# Linux/Mac
chmod +x pre-commit-check.sh
./pre-commit-check.sh

# Verifica:
# - Que .env no esté trackeado
# - Sintaxis de PHP
# - Que los tests pasen
# - Estilo de código
# - Archivos grandes
```

## 📦 Estructura del proyecto

```
registrovideo/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── YoutubeJsonTrackerController.php  # CRUD de videos
│   │   │   ├── DashboardController.php           # Dashboard con estadísticas
│   │   │   └── CategorySetupController.php       # Configuración inicial
│   │   └── Requests/
│   │       └── VideoStoreRequest.php             # Validación de formularios
│   └── Models/
│       ├── YoutubeJsonTracker.php                # Modelo principal de videos
│       └── VideoSocialPublication.php            # Publicaciones sociales
├── database/
│   ├── migrations/                               # 17 migraciones (esquema completo)
│   └── factories/                                # Factories para testing
├── resources/
│   ├── views/
│   │   ├── videos/                               # Vistas CRUD
│   │   ├── dashboard.blade.php                   # Dashboard principal
│   │   └── category-setup.blade.php              # Configuración inicial
│   └── js/app.js
├── tests/
│   └── Feature/
│       └── YoutubeJsonTrackerTest.php            # Suite de tests completa
├── .env.production.example                       # Plantilla para producción
├── nginx.conf.example                            # Configuración Nginx
└── SECURITY_AUDIT.md                             # Auditoría de seguridad
```

## 🔐 Seguridad

El proyecto ha sido auditado y cumple con los estándares de seguridad de Laravel:

- ✅ Protección CSRF en todos los formularios
- ✅ Protección contra SQL Injection (Eloquent ORM)
- ✅ Protección XSS (escape automático de Blade)
- ✅ Validación de inputs con FormRequest
- ✅ Rate limiting en rutas críticas
- ✅ Aislamiento de datos por usuario
- ✅ Limpieza automática de archivos huérfanos

Lee el análisis completo en [SECURITY_AUDIT.md](SECURITY_AUDIT.md).

## 🚀 Despliegue a producción

### Pre-requisitos

1. Copia y configura el `.env` de producción:

```bash
cp .env.production.example .env
nano .env  # Ajustar credenciales
```

2. Instala dependencias para producción:

```bash
composer install --no-dev --optimize-autoloader
npm run build
```

3. Configura permisos:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

4. Optimiza Laravel:

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

5. Configura tu servidor web (ver `nginx.conf.example`)

**Checklist completo**: consulta [SECURITY_AUDIT.md](SECURITY_AUDIT.md#-checklist-para-producción)

## 🗃️ Base de datos

El proyecto usa un esquema normalizado con 10 tablas:

**Principales:**
- `youtube_json_trackers` — videos registrados
- `video_social_publications` — publicaciones por red social
- `users` — usuarios autenticados

**Laravel:**
- `sessions`, `cache`, `jobs`, `failed_jobs`, `password_reset_tokens`, `cache_locks`

## 🎨 Funcionalidades destacadas

### Dashboard con estadísticas
- Filtros: Todos / Último mes / Trimestre / Año / Personalizado
- Contadores por plataforma (YouTube, Facebook, TikTok, Instagram)
- Últimos 5 registros del periodo

### Búsqueda inteligente
- Búsqueda por texto (título, modelo, marca, error)
- Filtros: tipo de aparato, red social, formato
- Sugerencias relacionadas por similitud

### Gestión multi-plataforma
- Campos separados por YouTube, Facebook, TikTok, Instagram
- Estado de publicación por plataforma
- Miniaturas independientes
- Cálculo automático de mes/año por fecha de publicación

## � Capturas de pantalla

### Dashboard
![Dashboard](docs/screenshots/dashboard.png)
*Dashboard con estadísticas por periodo y filtros*

### Historial de videos
![Historial](docs/screenshots/historial.png)
*Búsqueda avanzada con sugerencias relacionadas*

### Formulario de registro
![Formulario](docs/screenshots/formulario.png)
*Formulario multi-plataforma con miniaturas*

> **Nota**: Las capturas de pantalla se agregarán próximamente en la carpeta `docs/screenshots/`

## �🐛 Solución de problemas

### Error: "No application encryption key has been specified"
```bash
php artisan key:generate
```

### Error: "SQLSTATE[HY000] [1045] Access denied"
Verifica las credenciales de MySQL en tu `.env`

### Assets no se cargan (CSS/JS)
```bash
npm run build
php artisan config:clear
```

### Imágenes no se muestran
```bash
php artisan storage:link
# Verifica que existe: public/storage -> storage/app/public
```

### Tests fallan con error de BD
Crea la base de datos de testing:
```bash
mysql -u root -p -e "CREATE DATABASE registrovideo_testing;"
```

## 📚 Tecnologías utilizadas

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade Templates + Tailwind CSS
- **Base de datos**: MySQL 8.0+
- **Autenticación**: Laravel Breeze
- **Build tools**: Vite + PostCSS
- **Testing**: PHPUnit + Laravel Testing

## 📝 Changelog

### v1.0.0 (2026-08-27)
- ✅ Sistema completo de CRUD de videos
- ✅ Dashboard con estadísticas por periodo
- ✅ Búsqueda avanzada y filtros
- ✅ Soporte multi-plataforma (YouTube, Facebook, TikTok, Instagram)
- ✅ Gestión de miniaturas
- ✅ Rate limiting y seguridad reforzada
- ✅ 28 tests automatizados
- ✅ Auditoría de seguridad completa

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto es software privado. Todos los derechos reservados.

## 👤 Autor

Desarrollado para gestión de contenido de videos de reparación.

## 🙏 Agradecimientos

- [Laravel Framework](https://laravel.com) por el excelente framework PHP
- [Tailwind CSS](https://tailwindcss.com) por el sistema de diseño
- [Laravel Breeze](https://github.com/laravel/breeze) por la autenticación

## 📞 Soporte

Para reportar problemas o sugerencias, abre un issue en el repositorio.

## 🔗 Comandos Git útiles

```bash
# Primera vez - subir a GitHub
git init
git add .
git commit -m "Initial commit: Sistema de gestión de videos"
git remote add origin https://github.com/tu-usuario/registrovideo.git
git branch -M main
git push -u origin main

# Flujo diario
git status                              # Ver cambios
git add .                               # Agregar todo
git commit -m "feat: descripción"       # Commit
git push origin main                    # Subir a GitHub

# Ver más comandos
cat GIT_COMMANDS.md
```

---

**Nota**: Este proyecto requiere ajustes de configuración antes de desplegar a producción. Consulta [SECURITY_AUDIT.md](SECURITY_AUDIT.md) para el checklist completo.

