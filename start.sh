#!/usr/bin/env bash
set -e

echo "==> Creando directorios de almacenamiento..."
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/framework/cache/data
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/logs

echo "==> Limpiando config cache para usar variables de entorno actuales..."
php artisan config:clear --quiet
php artisan cache:clear --quiet

echo "==> Esperando conexión a PostgreSQL..."
MAX_TRIES=30
TRIES=0
until php -r "
\$host = getenv('DB_HOST') ?: '127.0.0.1';
\$port = getenv('DB_PORT') ?: '5432';
\$db   = getenv('DB_DATABASE') ?: 'railway';
\$user = getenv('DB_USERNAME') ?: 'postgres';
\$pass = getenv('DB_PASSWORD') ?: '';
\$ssl  = getenv('DB_SSLMODE') ?: 'prefer';
try {
    new PDO(\"pgsql:host=\$host;port=\$port;dbname=\$db;sslmode=\$ssl\", \$user, \$pass);
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" > /dev/null 2>&1; do
    TRIES=$((TRIES + 1))
    if [ $TRIES -ge $MAX_TRIES ]; then
        echo "ERROR: No se pudo conectar a PostgreSQL después de ${MAX_TRIES} intentos."
        exit 1
    fi
    echo "   Intento ${TRIES}/${MAX_TRIES} — esperando 2s..."
    sleep 2
done
echo "   Conexión establecida."

echo "==> Re-cacheando configuración..."
php artisan config:cache --quiet

echo "==> Migraciones centrales..."
php artisan migrate --force --no-interaction

echo "==> Migraciones de tenants..."
php artisan tenants:migrate --force --no-interaction

echo "==> Iniciando servidor en puerto ${PORT:-8080}..."
if [ -f /start-container.sh ]; then
    export SERVER_NAME=":${PORT:-8080}"
    echo "   FrankenPHP en SERVER_NAME=${SERVER_NAME}"
    exec /start-container.sh
else
    exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
fi
