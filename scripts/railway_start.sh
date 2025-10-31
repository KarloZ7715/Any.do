#!/usr/bin/env bash
set -euo pipefail

PORT=${PORT:-8080}

echo "[railway_start] Starting app on 0.0.0.0:${PORT}"

# Optionally run migrations (set RUN_MIGRATIONS=true in Railway env to enable)
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  echo "[railway_start] Running migrations"
  php artisan migrate --force
fi

# Choose server: USE_PHP_SERVER=true to use php -S (simple), otherwise artisan serve
if [ "${USE_PHP_SERVER:-false}" = "true" ]; then
  echo "[railway_start] Using php built-in server"
  exec php -S 0.0.0.0:${PORT} -t public/
else
  echo "[railway_start] Using artisan serve"
  exec php artisan serve --host=0.0.0.0 --port=${PORT}
fi
