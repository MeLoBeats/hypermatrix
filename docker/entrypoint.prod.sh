#!/bin/bash
set -e
cd /var/www/html

echo "Booting Laravel (prod)..."

# Exiger APP_KEY en prod pour éviter un boot instable
if [ -z "${APP_KEY}" ]; then
  echo "ERROR: APP_KEY is not set. Provide it via environment (e.g., docker secrets or env)."
  exit 1
fi

# Liens de stockage (si non présent). Ne casse pas si déjà fait.
if [ ! -L public/storage ]; then
  php artisan storage:link || true
fi

# Purger caches potentiellement copiés depuis le host
rm -f bootstrap/cache/*.php || true

# Empêcher Vite "hot" en prod
rm -f public/hot || true

# Recréer le manifest des packages à partir des *packages réellement installés*
php artisan package:discover --ansi || true


# Caches prod – ne touche pas à la DB
php artisan config:clear >/dev/null 2>&1 || true
php artisan route:clear  >/dev/null 2>&1 || true
php artisan view:clear   >/dev/null 2>&1 || true

php artisan config:cache || echo "WARN: config:cache failed"
php artisan route:cache  || echo "WARN: route:cache failed"
php artisan view:cache   || echo "WARN: view:cache failed"

# Optionnel: migrations automatiques sous contrôle d'un flag
if [ "${RUN_MIGRATIONS}" = "true" ]; then
  echo "Running database migrations (RUN_MIGRATIONS=true)..."
  php artisan migrate --force || (echo "ERROR: migrations failed" && exit 1)
fi

echo "Laravel ready."
exec "$@"
