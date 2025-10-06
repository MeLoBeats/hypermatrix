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

  # Attendre que MySQL soit joignable sur TCP avant d'exécuter les migrations
  DB_HOST="${DB_HOST:-localhost}"
  DB_PORT="${DB_PORT:-3306}"
  echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
  for i in $(seq 1 60); do
    if (echo > /dev/tcp/${DB_HOST}/${DB_PORT}) >/dev/null 2>&1; then
      echo "MySQL is reachable."
      break
    fi
    if [ "$i" -eq 60 ]; then
      echo "ERROR: Timed out waiting for MySQL at ${DB_HOST}:${DB_PORT}"
      exit 1
    fi
    sleep 1
  done

  # Optionnel: attendre que le schéma soit prêt (moteur initialisé)
  # On re-tente la commande migrate:status en silence pour valider la connexion
  set +e
  for i in $(seq 1 10); do
    php artisan migrate:status >/dev/null 2>&1 && STATUS_OK=1 && break || STATUS_OK=0
    sleep 1
  done
  set -e
  if [ "${STATUS_OK}" != "1" ]; then
    echo "WARN: DB reachable but artisan can't connect yet; proceeding to migrate with retry..."
  fi

  # Exécuter les migrations avec un petit retry simple si la première tentative échoue
  php artisan migrate --force || {
    echo "WARN: First migrate attempt failed, retrying in 3s..."
    sleep 3
    php artisan migrate --force || (echo "ERROR: migrations failed" && exit 1)
  }
fi

echo "Laravel ready."
exec "$@"
