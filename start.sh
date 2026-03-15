#!/usr/bin/env bash
set -e

echo "==> Migraciones centrales..."
php artisan migrate --force --no-interaction

echo "==> Migraciones de tenants..."
php artisan tenants:migrate --force --no-interaction

echo "==> Optimizando..."
php artisan optimize --quiet

echo "==> Iniciando servidor en puerto ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
