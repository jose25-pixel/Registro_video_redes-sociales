#!/bin/bash

# Script de verificación pre-commit para RegistroVideo
# Uso: ./pre-commit-check.sh

set -e

echo "======================================"
echo "🔍 Verificación Pre-Commit"
echo "======================================"
echo ""

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERRORS=0

# 1. Verificar que .env no esté en el repo
echo -n "🔐 Verificando que .env no esté trackeado... "
if git ls-files | grep -q "^\.env$"; then
    echo -e "${RED}❌ FALLO${NC}"
    echo "   El archivo .env está en el repositorio. Elimínalo con:"
    echo "   git rm --cached .env"
    ERRORS=$((ERRORS + 1))
else
    echo -e "${GREEN}✅ OK${NC}"
fi

# 2. Verificar que .env.production no esté en el repo
echo -n "🔐 Verificando que .env.production no esté trackeado... "
if git ls-files | grep -q "^\.env\.production$"; then
    echo -e "${RED}❌ FALLO${NC}"
    echo "   El archivo .env.production está en el repositorio. Elimínalo con:"
    echo "   git rm --cached .env.production"
    ERRORS=$((ERRORS + 1))
else
    echo -e "${GREEN}✅ OK${NC}"
fi

# 3. Verificar sintaxis PHP
echo -n "🔧 Verificando sintaxis de PHP... "
if find app -name "*.php" -exec php -l {} \; 2>&1 | grep -q "Parse error"; then
    echo -e "${RED}❌ FALLO${NC}"
    echo "   Hay errores de sintaxis en archivos PHP"
    ERRORS=$((ERRORS + 1))
else
    echo -e "${GREEN}✅ OK${NC}"
fi

# 4. Ejecutar tests
echo -n "🧪 Ejecutando tests... "
if php artisan test --without-output 2>&1 | grep -q "FAILED"; then
    echo -e "${RED}❌ FALLO${NC}"
    echo "   Algunos tests están fallando. Ejecuta: php artisan test"
    ERRORS=$((ERRORS + 1))
else
    echo -e "${GREEN}✅ OK${NC}"
fi

# 5. Verificar PSR-12 (opcional, requiere PHP CS Fixer)
if command -v php-cs-fixer >/dev/null 2>&1; then
    echo -n "📏 Verificando estilo de código PSR-12... "
    if php-cs-fixer fix --dry-run --diff app 2>&1 | grep -q "Legend:"; then
        echo -e "${YELLOW}⚠️  ADVERTENCIA${NC}"
        echo "   Hay problemas de estilo. Ejecuta: php-cs-fixer fix app"
    else
        echo -e "${GREEN}✅ OK${NC}"
    fi
fi

# 6. Verificar que composer.lock esté actualizado
echo -n "📦 Verificando composer.lock... "
if git diff --quiet composer.json composer.lock; then
    echo -e "${GREEN}✅ OK${NC}"
else
    if git diff --quiet composer.json; then
        echo -e "${GREEN}✅ OK${NC}"
    else
        echo -e "${YELLOW}⚠️  ADVERTENCIA${NC}"
        echo "   composer.json fue modificado. Ejecuta: composer update"
    fi
fi

# 7. Verificar que package-lock.json esté actualizado
echo -n "📦 Verificando package-lock.json... "
if git diff --quiet package.json package-lock.json; then
    echo -e "${GREEN}✅ OK${NC}"
else
    if git diff --quiet package.json; then
        echo -e "${GREEN}✅ OK${NC}"
    else
        echo -e "${YELLOW}⚠️  ADVERTENCIA${NC}"
        echo "   package.json fue modificado. Ejecuta: npm install"
    fi
fi

# 8. Verificar archivos grandes
echo -n "📂 Verificando tamaño de archivos... "
LARGE_FILES=$(find . -type f -size +5M ! -path "./vendor/*" ! -path "./node_modules/*" ! -path "./.git/*")
if [ -n "$LARGE_FILES" ]; then
    echo -e "${YELLOW}⚠️  ADVERTENCIA${NC}"
    echo "   Archivos grandes encontrados (>5MB):"
    echo "$LARGE_FILES"
else
    echo -e "${GREEN}✅ OK${NC}"
fi

# 9. Verificar console.log en JavaScript (opcional)
echo -n "🔍 Verificando console.log en JS... "
if find resources/js -name "*.js" -exec grep -q "console.log" {} \;; then
    echo -e "${YELLOW}⚠️  ADVERTENCIA${NC}"
    echo "   Hay console.log en archivos JavaScript"
else
    echo -e "${GREEN}✅ OK${NC}"
fi

# 10. Verificar dd() o dump() en código PHP
echo -n "🔍 Verificando dd() o dump() en PHP... "
if find app -name "*.php" -exec grep -E "(dd\(|dump\()" {} \; | grep -v "//"; then
    echo -e "${YELLOW}⚠️  ADVERTENCIA${NC}"
    echo "   Hay dd() o dump() en código PHP"
else
    echo -e "${GREEN}✅ OK${NC}"
fi

echo ""
echo "======================================"

if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✅ Todo listo para commit${NC}"
    echo "======================================"
    exit 0
else
    echo -e "${RED}❌ $ERRORS error(es) encontrado(s)${NC}"
    echo "======================================"
    echo ""
    echo "Corrige los errores antes de hacer commit"
    exit 1
fi
