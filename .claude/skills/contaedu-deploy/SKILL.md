---
name: contaedu-deploy
description: >-
  Checklist de deploy a Railway para ContaEdu. Activar cuando se vayan
  a hacer cambios en producción, nuevas migraciones, o cambios de
  configuración. Cubre variables de entorno requeridas, orden de
  migraciones, AutoMigrateTenant, verificación post-deploy y rollback.
---

# ContaEdu — Deploy a Railway

## Arquitectura de despliegue

```
Railway (producción)
  ├── Service: contaedu (Laravel app)
  │     ├── Build: Nixpacks (auto-detecta PHP/Composer/Node)
  │     └── Pre-deploy step: php artisan migrate --force
  └── Service: PostgreSQL
        └── BD: railway (schema public = central)
              └── schemas por tenant: tenant_cc1023456789, tenant_cc9876543210, ...
```

---

## Variables de entorno requeridas en Railway

```bash
APP_NAME=ContaEdu
APP_ENV=production
APP_KEY=base64:XXXX...   # php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://tu-app.railway.app

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_CO

# Logging — NUNCA usar 'single' o 'stack' (escribe en disco, Railway es efímero)
LOG_CHANNEL=stderr
LOG_LEVEL=error

# BD — usar red interna de Railway (más rápido, sin costo de egress)
DB_CONNECTION=pgsql
DB_HOST=postgres.railway.internal
DB_PORT=5432
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=<generado por Railway>

# Sesiones en cookie (sin tabla sessions, más simple)
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true

# Caché en BD, colas síncronas
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log

# Path de vistas compiladas (crítico en contenedores Railway)
VIEW_COMPILED_PATH=/app/storage/framework/views

# Livewire uploads temporales
LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local
LIVEWIRE_TEMPORARY_FILE_UPLOAD_DIRECTORY=livewire-tmp

# Correo (opcional, si se necesita notificaciones)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@contaedu.app"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

---

## Pre-deploy step (NO Start Command)

En Railway, las migraciones van en el **Pre-deploy step**, no en el Start Command.
El Start Command es solo para arrancar el servidor.

```bash
# Pre-deploy step (Railway > Service > Settings > Pre-deploy command)
php artisan migrate --force

# Start Command (Railway > Service > Settings > Start command)
# Railway lo detecta automáticamente con Nixpacks — usualmente:
# php artisan serve --host=0.0.0.0 --port=$PORT
# O con Apache/Nginx configurado por Nixpacks
```

**Por qué Pre-deploy y no Start Command:**
- El Pre-deploy corre ANTES de que la instancia nueva empiece a recibir tráfico
- Si la migración falla, Railway mantiene la instancia anterior activa
- El Start Command corre en cada instancia nueva — si hay múltiples instancias, las migraciones correrían varias veces en paralelo

---

## Migraciones — qué corre dónde

```
php artisan migrate --force
  ↓
Aplica: database/migrations/*.php  (central, BD pública)
NO aplica: database/migrations/tenant/*.php

Las migraciones tenant son automáticas via AutoMigrateTenant:
  → La primera vez que un estudiante hace login post-deploy
  → AutoMigrateTenant detecta migraciones pendientes y las corre en su schema
```

---

## AutoMigrateTenant — comportamiento en producción

```
Estudiante hace login → InitializeTenancyByStudent → TenancyBootstrapped
  → AutoMigrateTenant::handle()
    ├── ¿Schema nuevo? → corre todas las migraciones + seedCapitalInicial()
    └── ¿Schema existente? → corre solo las migraciones pendientes (nuevas)
```

**No se necesita correr `tenants:migrate` manualmente en Railway.**
El deploy automático aplica las migraciones nuevas en el primer acceso de cada estudiante.

Si necesitas aplicar una migración tenant urgente a TODOS los estudiantes de golpe:

```bash
# En Railway console (Service → Deploy → Shell)
php artisan tenants:migrate-all
```

---

## Checklist pre-deploy

### Código
- [ ] Branch `feature/*` mergeado a `main`
- [ ] Tests pasan localmente: `php artisan test`
- [ ] No hay `dd()`, `dump()`, `ray()`, `var_dump()` en el código
- [ ] `APP_DEBUG=false` en Railway (verificar que no está en `true`)

### Migraciones
- [ ] Las migraciones centrales nuevas van en `database/migrations/` (raíz)
- [ ] Las migraciones tenant nuevas van en `database/migrations/tenant/`
- [ ] Cada migración tenant tiene guard `hasTable`/`hasColumn`
- [ ] Migraciones probadas localmente con `php artisan migrate`

### Variables de entorno
- [ ] `APP_KEY` está configurado (sin esto Laravel lanza error de cifrado)
- [ ] `DB_PASSWORD` coincide con el PostgreSQL de Railway
- [ ] `SESSION_SECURE_COOKIE=true` (HTTPS en producción)
- [ ] `LOG_CHANNEL=stderr` (no `single` ni `stack`)

---

## Checklist post-deploy

### Funcional
- [ ] `https://tu-app.railway.app/login` carga sin error
- [ ] Login de admin funciona
- [ ] Login de docente funciona
- [ ] Login de estudiante funciona
- [ ] Al entrar a `/empresa/dashboard` o `/aprendizaje/facturas` → no hay error 500

### Migraciones
- [ ] `https://tu-app.railway.app` → Railway muestra el deploy como "Success"
- [ ] Si hay migraciones tenant nuevas → acceder con un estudiante de prueba y verificar
      que las nuevas columnas/tablas existen

### Logs en Railway
- [ ] Railway → Service → Logs → no hay excepciones en los últimos 2 minutos
- [ ] Si hay errores 500 → revisar el stack trace en los logs

---

## Errores comunes en producción

### "No application encryption key has been specified"
```bash
# APP_KEY no está configurado en Railway
php artisan key:generate --show
# Copiar el resultado como variable APP_KEY en Railway
```

### "SQLSTATE[08006] — could not connect to server"
```bash
# DB_HOST incorrecto — usar red interna de Railway
DB_HOST=postgres.railway.internal  # ✅ red interna (más rápido)
# NO usar el host público (más lento y puede fallar por firewall de Railway)
```

### Vista Livewire en blanco / error de Alpine
```bash
# Vite no compiló los assets — verificar que el build de Nixpacks corrió npm run build
# Railway debería hacerlo automáticamente si detecta package.json
```

### "The view [livewire.tenant.X] not found"
```bash
# Las vistas compiladas están en un path incorrecto
# Verificar VIEW_COMPILED_PATH=/app/storage/framework/views
```

### Error 419 en formularios POST
```bash
# SESSION_DRIVER=cookie funciona bien en Railway
# Verificar SESSION_SECURE_COOKIE=true (HTTPS)
# Verificar que SESSION_DOMAIN=null (no el dominio de Railway)
```

### AutoMigrateTenant falla silenciosamente
```bash
# Revisar Railway logs cuando el estudiante intenta acceder
# El error más común: migración sin guard hasColumn() que falla en schema existente
# Fix: agregar guard y hacer redeploy
```

---

## Rollback de emergencia

Railway mantiene el deploy anterior. Para hacer rollback:

```
Railway → Service → Deployments → seleccionar deploy anterior → Redeploy
```

Si el rollback involucra revertir una migración de BD:
1. **NO** usar `migrate:rollback` en producción sin verificar impacto en datos
2. Si la migración solo agrega columnas nullable → se puede mantener la migración nueva y hacer rollback del código
3. Si la migración modifica datos → evaluar caso por caso

---

## Ambiente local vs Railway

| Aspecto | Local (Herd) | Railway |
|---------|-------------|---------|
| Servidor | Herd (Nginx/PHP-FPM) | Nixpacks (auto) |
| BD host | `127.0.0.1` | `postgres.railway.internal` |
| Debug | `APP_DEBUG=true` | `APP_DEBUG=false` |
| Logs | `storage/logs/laravel.log` | stderr → Railway Logs |
| Sessions | `cookie` o `file` | `cookie` |
| Assets | `npm run dev` (Vite HMR) | `npm run build` (Vite build) |
| Migraciones tenant | Manual `php artisan migrate` | Automático via AutoMigrateTenant |

---

## Comandos útiles en Railway console

```bash
# Ver estado de migraciones centrales
php artisan migrate:status

# Aplicar migraciones tenant a todos los estudiantes (urgente)
php artisan tenants:migrate-all

# Limpiar caché de configuración (si cambiaron variables de entorno)
php artisan config:clear && php artisan cache:clear

# Ver todos los tenants registrados
php artisan tinker --execute="echo App\Models\Central\Tenant::count() . ' tenants';"
```
