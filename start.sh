#!/usr/bin/env bash
set -e

echo "==> Creando directorios de almacenamiento..."
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/framework/cache/data
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/logs

echo "==> Esperando conexión a PostgreSQL..."
MAX_TRIES=30
TRIES=0
until php artisan db:show --json > /dev/null 2>&1; do
    TRIES=$((TRIES + 1))
    if [ $TRIES -ge $MAX_TRIES ]; then
        echo "ERROR: No se pudo conectar a PostgreSQL después de ${MAX_TRIES} intentos."
        exit 1
    fi
    echo "   Intento ${TRIES}/${MAX_TRIES} — esperando 2s..."
    sleep 2
done
echo "   Conexión establecida."

echo "==> Migraciones centrales..."
php artisan migrate --force --no-interaction

echo "==> Migraciones de tenants..."
php artisan tenants:migrate --force --no-interaction

echo "==> Iniciando servidor en puerto ${PORT:-8080}..."
if [ -f /start-container.sh ]; then
    # FrankenPHP necesita SERVER_NAME para escuchar en el puerto de Railway
    export SERVER_NAME=":${PORT:-8080}"
    echo "   FrankenPHP en SERVER_NAME=${SERVER_NAME}"
    exec /start-container.sh
else
    exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
fi
