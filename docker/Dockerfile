# ---------- STAGE 1: PHP DEPENDENCIES ----------
FROM composer:2 AS vendor
WORKDIR /app

# Copier uniquement ce qui est nécessaire pour calculer la résolution des deps
COPY composer.json composer.lock ./
# Installer sans dev, sans interaction
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-progress --no-scripts

# Copier le projet pour que l'autoload final prenne tout en compte
COPY . .
# Si tu as besoin d'exécuter des scripts post-install, retire --no-scripts ci-dessus et lance:
# RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-progress

# ---------- STAGE 2: FRONT BUILD ----------
FROM node:20-alpine AS assets
WORKDIR /app

# 1) Déps front
COPY package.json package-lock.json* yarn.lock* pnpm-lock.yaml* ./
RUN if [ -f package-lock.json ]; then npm ci; \
    elif [ -f yarn.lock ]; then yarn install --frozen-lockfile; \
    elif [ -f pnpm-lock.yaml ]; then corepack enable && pnpm i --frozen-lockfile; \
    else npm i; fi

# 2) Copier le code nécessaire au build (on s’appuie sur .dockerignore pour ne pas tout amener)
COPY . .

# 3) Build seulement si Vite est présent (sinon on n'échoue pas)
RUN if [ -f vite.config.ts ] || [ -f vite.config.js ]; then npm run build; \
    else echo "No Vite config found -> skipping asset build"; fi

# ---------- STAGE 3: RUNTIME ----------
FROM webdevops/php-nginx:8.4-alpine

# Config Nginx/PHP
ENV WEB_DOCUMENT_ROOT=/var/www/html/public \
    WEB_DOCUMENT_INDEX=index.php \
    PHP_DATE_TIMEZONE=UTC \
    PHP_MEMORY_LIMIT=512M \
    PHP_POST_MAX_SIZE=50M \
    PHP_UPLOAD_MAX_FILESIZE=50M \
    APP_ENV=production

WORKDIR /var/www/html

# Outils utiles (minimaux) – pas de node/npm ni mysql-client en prod
RUN apk add --no-cache bash curl

# Copier le code applicatif (sans node_modules etc. via .dockerignore)
COPY --chown=application:application . .

# Copier les vendors et le build front depuis les stages
COPY --from=vendor --chown=application:application /app/vendor ./vendor
COPY --from=assets --chown=application:application /app/public/build ./public/build

# Config Nginx/PHP personnalisées (si présentes)
COPY docker/nginx/default.conf /opt/docker/etc/nginx/vhost.conf
COPY docker/php/local.ini       /usr/local/etc/php/conf.d/local.ini
COPY docker/php/www.conf        /usr/local/etc/php-fpm.d/www.conf

# Permissions Laravel
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache \
    && chown -R application:application storage bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache

# Entrypoint minimal prod
COPY docker/entrypoint.prod.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9001
HEALTHCHECK --interval=30s --timeout=3s --retries=3 CMD curl -fsS http://localhost:9001/ || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/opt/docker/bin/entrypoint.sh", "supervisord"]
