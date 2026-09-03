# Script de instalación para RegistroVideo (Windows)
# Uso: .\install.ps1

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "📹 RegistroVideo - Instalación" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# Verificar requisitos
$commands = @{
    "php" = "PHP"
    "composer" = "Composer"
    "npm" = "npm"
}

foreach ($cmd in $commands.Keys) {
    try {
        & $cmd --version 2>&1 | Out-Null
        Write-Host "✅ $($commands[$cmd]) encontrado" -ForegroundColor Green
    } catch {
        Write-Host "❌ Error: $($commands[$cmd]) no está instalado" -ForegroundColor Red
        exit 1
    }
}

Write-Host ""

# Instalar dependencias de Composer
Write-Host "📦 Instalando dependencias de PHP..." -ForegroundColor Yellow
composer install
if ($LASTEXITCODE -ne 0) { exit 1 }

# Instalar dependencias de npm
Write-Host "📦 Instalando dependencias de JavaScript..." -ForegroundColor Yellow
npm install
if ($LASTEXITCODE -ne 0) { exit 1 }

# Copiar archivo de entorno
if (-not (Test-Path .env)) {
    Write-Host "📝 Copiando archivo .env..." -ForegroundColor Yellow
    Copy-Item .env.example .env
} else {
    Write-Host "⚠️  Archivo .env ya existe, no se sobreescribirá" -ForegroundColor Yellow
}

# Generar clave de aplicación
Write-Host "🔑 Generando clave de aplicación..." -ForegroundColor Yellow
php artisan key:generate

# Solicitar credenciales de base de datos
Write-Host ""
Write-Host "🗃️  Configuración de base de datos" -ForegroundColor Cyan
Write-Host "-----------------------------------" -ForegroundColor Cyan

$DB_NAME = Read-Host "Nombre de la base de datos [registrovideo]"
if ([string]::IsNullOrWhiteSpace($DB_NAME)) { $DB_NAME = "registrovideo" }

$DB_USER = Read-Host "Usuario de MySQL [root]"
if ([string]::IsNullOrWhiteSpace($DB_USER)) { $DB_USER = "root" }

$DB_PASS = Read-Host "Contraseña de MySQL" -AsSecureString
$DB_PASS_Plain = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
    [Runtime.InteropServices.Marshal]::SecureStringToBSTR($DB_PASS)
)

# Actualizar .env
$envContent = Get-Content .env -Raw
$envContent = $envContent -replace "DB_DATABASE=.*", "DB_DATABASE=$DB_NAME"
$envContent = $envContent -replace "DB_USERNAME=.*", "DB_USERNAME=$DB_USER"
$envContent = $envContent -replace "DB_PASSWORD=.*", "DB_PASSWORD=$DB_PASS_Plain"
Set-Content .env -Value $envContent

Write-Host "✅ Archivo .env actualizado" -ForegroundColor Green

# Preguntar por migraciones
Write-Host ""
$runMigrations = Read-Host "¿Ejecutar migraciones de base de datos? (s/n)"
if ($runMigrations -eq "s" -or $runMigrations -eq "S") {
    Write-Host "🔄 Ejecutando migraciones..." -ForegroundColor Yellow
    php artisan migrate
}

# Crear enlace simbólico de storage
Write-Host "🔗 Creando enlace simbólico de storage..." -ForegroundColor Yellow
php artisan storage:link

# Compilar assets
Write-Host "🎨 Compilando assets..." -ForegroundColor Yellow
npm run build

# Limpiar caché
Write-Host "🧹 Limpiando caché..." -ForegroundColor Yellow
php artisan config:clear
php artisan cache:clear
php artisan view:clear

Write-Host ""
Write-Host "======================================" -ForegroundColor Green
Write-Host "✅ Instalación completada" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Green
Write-Host ""
Write-Host "Para iniciar el servidor de desarrollo:" -ForegroundColor Cyan
Write-Host "  php artisan serve" -ForegroundColor White
Write-Host ""
Write-Host "La aplicación estará disponible en:" -ForegroundColor Cyan
Write-Host "  http://localhost:8000" -ForegroundColor White
Write-Host ""
Write-Host "Para desarrollo con hot reload:" -ForegroundColor Cyan
Write-Host "  Terminal 1: php artisan serve" -ForegroundColor White
Write-Host "  Terminal 2: npm run dev" -ForegroundColor White
Write-Host ""
