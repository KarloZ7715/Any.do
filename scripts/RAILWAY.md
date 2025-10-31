# Railway deploy notes for Any.do

This project uses Laravel (PHP) + Vite (Vue 3). To deploy on Railway, set the Build and Start commands and environment variables as below.

Recommended Railway settings

- Build Command:

  ```bash
  bash scripts/railway_build.sh
  ```

- Start Command:

  ```bash
  bash scripts/railway_start.sh
  ```

Environment variables (important)

- `APP_URL` = https://your-railway-url (e.g. https://anydo-production.up.railway.app)
- `FORCE_HTTPS` = true
- `APP_ENV` = production
- `RUN_MIGRATIONS` = true (optional — only if you want migrations to run on start)
- `USE_PHP_SERVER` = false (set to true to use PHP built-in server instead of `php artisan serve`)
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — set your DB details

Notes

- Ensure env vars are present for the build step if you use `php artisan config:cache` during build. The `railway_build.sh` script only runs `config:cache` when `APP_URL` is set to avoid caching wrong values.
- If your Railway service uses a buildpack that expects a `Procfile`, you can set the Start command there or configure Railway's Start Command.
- For production you should prefer a proper webserver (nginx + php-fpm). The scripts provided are a simple, portable approach for Railway's containers.
