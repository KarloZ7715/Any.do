#!/usr/bin/env bash
set -euo pipefail

echo "[railway_build] Starting build"

# Install PHP dependencies
if command -v composer >/dev/null 2>&1; then
  echo "[railway_build] Installing composer dependencies (no-dev)"
  composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
else
  echo "[railway_build] Warning: composer not found in PATH"
fi

# Install Node dependencies
if [ -f package-lock.json ] || [ -f pnpm-lock.yaml ]; then
  echo "[railway_build] Installing Node dependencies (ci)"
  if command -v npm >/dev/null 2>&1; then
    npm ci --silent
  else
    echo "[railway_build] Warning: npm not found in PATH"
  fi
else
  if command -v npm >/dev/null 2>&1; then
    echo "[railway_build] Installing Node dependencies (install)"
    npm install --silent
  fi
fi

# Build frontend (vite)
if command -v npm >/dev/null 2>&1; then
  echo "[railway_build] Running npm run build (if present)"
  npm run build --if-present
fi

# Generate app key if missing
if command -v php >/dev/null 2>&1; then
  echo "[railway_build] Ensuring APP_KEY exists"
  php artisan key:generate --force || true

  # Clear caches to avoid stale config during build
  php artisan config:clear || true
  php artisan route:clear || true
  php artisan view:clear || true
  php artisan cache:clear || true

  # Cache config/routes/views only if APP_URL is set (avoid embedding wrong env)
  if [ -n "${APP_URL:-}" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
  fi
fi

echo "[railway_build] Build finished"
