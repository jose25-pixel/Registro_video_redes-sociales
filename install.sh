#!/bin/bash

# Script de instalación para RegistroVideo
# Uso: ./install.sh

set -e

echo "======================================"
echo "📹 RegistroVideo - Instalación"
echo "======================================"
echo ""

# Verificar requisitos
command -v php >/dev/null 2>&1 || { echo "❌ Error: PHP no está instalado"; exit 1; }
command -v composer >/dev/null 2>&1 || { echo "❌ Error: Composer no está instalado"; exit 1; }
command -v npm >/dev/null 2>&1 || { echo "❌ Error: npm no está instalado"; exit 1; }

echo "✅ Requisitos verificados"
echo ""

# Instalar dependencias de Composer
echo "📦 Instalando dependencias de PHP..."
composer install

# Instalar dependencias de npm
echo "📦 Instalando dependencias de JavaScript..."
npm install

# Copiar archivo de entorno
if [ ! -f .env ]; then
    echo "📝 Copiando archivo .env..."
    cp .env.example .env
else
    echo "⚠️  Archivo .env ya existe, no se sobreescribirá"
fi

# Generar clave de aplicación
echo "🔑 Generando clave de aplicación..."
php artisan key:generate

# Solicitar credenciales de base de datos
echo ""
echo "🗃️  Configuración de base de datos"
echo "-----------------------------------"
read -p "Nombre de la base de datos [registrovideo]: " DB_NAME
DB_NAME=${DB_NAME:-registrovideo}

read -p "Usuario de MySQL [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -sp "Contraseña de MySQL: " DB_PASS
echo ""

# Actualizar .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

echo "✅ Archivo .env actualizado"

# Crear base de datos
echo ""
read -p "¿Deseas crear la base de datos automáticamente? (s/n): " CREATE_DB
if [ "$CREATE_DB" = "s" ] || [ "$CREATE_DB" = "S" ]; then
    echo "📊 Creando base de datos..."
    mysql -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || {
        echo "⚠️  No se pudo crear la base de datos automáticamente"
        echo "   Créala manualmente con: CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    }
fi

# Ejecutar migraciones
echo ""
read -p "¿Ejecutar migraciones de base de datos? (s/n): " RUN_MIGRATIONS
if [ "$RUN_MIGRATIONS" = "s" ] || [ "$RUN_MIGRATIONS" = "S" ]; then
    echo "🔄 Ejecutando migraciones..."
    php artisan migrate
fi

# Crear enlace simbólico de storage
echo "🔗 Creando enlace simbólico de storage..."
php artisan storage:link

# Compilar assets
echo "🎨 Compilando assets..."
npm run build

# Limpiar caché
echo "🧹 Limpiando caché..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo ""
echo "======================================"
echo "✅ Instalación completada"
echo "======================================"
echo ""
echo "Para iniciar el servidor de desarrollo:"
echo "  php artisan serve"
echo ""
echo "La aplicación estará disponible en:"
echo "  http://localhost:8000"
echo ""
echo "Para desarrollo con hot reload:"
echo "  Terminal 1: php artisan serve"
echo "  Terminal 2: npm run dev"
echo ""
