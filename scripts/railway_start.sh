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
# Prefer a production-ready server if available:
# 1) vendor/bin/heroku-php-apache2 (Heroku-style PHP helper)
# 2) php-fpm (if available)
# 3) php -S when USE_PHP_SERVER=true
# 4) fallback to artisan serve

if [ -x "vendor/bin/heroku-php-apache2" ]; then
  echo "[railway_start] Using vendor/bin/heroku-php-apache2 (recommended)"
  exec vendor/bin/heroku-php-apache2 public/
elif command -v php-fpm >/dev/null 2>&1; then
  echo "[railway_start] Using php-fpm (foreground)"
  # Try to run php-fpm in the foreground; this may depend on the environment PHP-FPM config
  exec php-fpm -F -R
elif [ "${USE_PHP_SERVER:-false}" = "true" ]; then
  echo "[railway_start] Using php built-in server"
  exec php -S 0.0.0.0:${PORT} -t public/
else
  echo "[railway_start] Using artisan serve (fallback)"
  exec php artisan serve --host=0.0.0.0 --port=${PORT}
fi
