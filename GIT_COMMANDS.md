# Comandos Git para RegistroVideo

## 🚀 Primera vez - Subir proyecto a GitHub

### 1. Inicializar Git (si no está inicializado)
```bash
git init
```

### 2. Verificar archivos ignorados
```bash
# Asegúrate de que .env NO esté en el repo
git status

# Si .env aparece, agrégalo al .gitignore:
echo ".env" >> .gitignore
```

### 3. Agregar archivos al staging
```bash
# Agregar todos los archivos
git add .

# O agregar archivos específicos
git add README.md
git add app/
git add resources/
```

### 4. Primer commit
```bash
git commit -m "Initial commit: Sistema de gestión de videos con Laravel 12"
```

### 5. Crear repositorio en GitHub
1. Ve a https://github.com/new
2. Nombre: `registrovideo`
3. Descripción: "Sistema de gestión y registro de videos para YouTube, Facebook, TikTok e Instagram"
4. Visibilidad: Privado (recomendado) o Público
5. **NO** marques "Add a README file"
6. Haz clic en "Create repository"

### 6. Conectar con el repositorio remoto
```bash
# Reemplaza 'tu-usuario' con tu nombre de usuario de GitHub
git remote add origin https://github.com/tu-usuario/registrovideo.git

# Verificar
git remote -v
```

### 7. Subir código a GitHub
```bash
# Primera vez (crea la rama main y sube)
git branch -M main
git push -u origin main
```

## 📝 Comandos diarios

### Verificar estado
```bash
git status
```

### Agregar cambios
```bash
# Todos los cambios
git add .

# Archivo específico
git add app/Http/Controllers/MiController.php
```

### Hacer commit
```bash
git commit -m "Descripción clara del cambio"
```

### Subir cambios a GitHub
```bash
git push origin main
```

## 🔄 Flujo completo (después de hacer cambios)
```bash
git status                              # Ver qué cambió
git add .                               # Agregar todo
git commit -m "Descripción del cambio"  # Commit
git push origin main                    # Subir a GitHub
```

## 📦 Comandos útiles

### Ver historial de commits
```bash
git log --oneline
```

### Ver diferencias antes de commit
```bash
git diff
```

### Deshacer cambios no guardados
```bash
# Deshacer cambios en un archivo
git checkout -- archivo.php

# Deshacer todos los cambios no guardados
git reset --hard
```

### Crear una nueva rama
```bash
# Crear y cambiar a nueva rama
git checkout -b feature/nueva-funcionalidad

# Subir la nueva rama
git push -u origin feature/nueva-funcionalidad
```

### Volver a la rama main
```bash
git checkout main
```

### Actualizar desde GitHub
```bash
git pull origin main
```

## ⚠️ Importante - Archivos que NO deben subirse

Estos archivos están en `.gitignore` y **nunca** deben subirse a Git:

- ❌ `.env` (contiene passwords y secretos)
- ❌ `.env.production` (credenciales de producción)
- ❌ `/vendor` (dependencias de Composer)
- ❌ `/node_modules` (dependencias de npm)
- ❌ `/public/storage` (enlace simbólico)
- ❌ `storage/*.key` (claves de encriptación)

### Verificar que .env no esté trackeado
```bash
# Este comando NO debe mostrar .env
git ls-files | grep .env

# Si aparece, eliminarlo del tracking:
git rm --cached .env
git commit -m "Remove .env from tracking"
```

## 🔐 Configurar credenciales de Git (primera vez)

```bash
git config --global user.name "Tu Nombre"
git config --global user.email "tu-email@ejemplo.com"

# Verificar
git config --list
```

## 🌿 Mensajes de commit recomendados

```bash
# Nuevas funcionalidades
git commit -m "feat: Agregar dashboard con estadísticas por periodo"

# Corrección de bugs
git commit -m "fix: Corregir conteo de videos publicados en Facebook"

# Mejoras de rendimiento
git commit -m "perf: Cambiar cache de database a file"

# Seguridad
git commit -m "sec: Agregar rate limiting en rutas de CRUD"

# Documentación
git commit -m "docs: Actualizar README con instrucciones de instalación"

# Tests
git commit -m "test: Agregar prueba para limpieza de miniaturas"
```

## 🆘 Solución de problemas

### Error: "fatal: not a git repository"
```bash
git init
```

### Error: "remote origin already exists"
```bash
git remote remove origin
git remote add origin https://github.com/tu-usuario/registrovideo.git
```

### Error: "failed to push some refs"
```bash
# Primero actualizar
git pull origin main --rebase

# Luego subir
git push origin main
```

### Olvidé hacer commit y quiero revertir
```bash
git reset --hard HEAD
```

### Necesito cambiar el último mensaje de commit
```bash
git commit --amend -m "Nuevo mensaje"
```

## 🔗 Enlaces útiles

- [GitHub Desktop](https://desktop.github.com/) - Interfaz gráfica para Git
- [Git Cheat Sheet](https://education.github.com/git-cheat-sheet-education.pdf)
- [Learn Git Branching](https://learngitbranching.js.org/) - Tutorial interactivo
