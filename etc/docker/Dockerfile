# Production image: Octane on FrankenPHP with PHP 8.5, compiled assets and no
# development dependencies. Build from the repository root:
#   docker build -f etc/docker/Dockerfile -t laravel-one-shot .
# Configuration comes from environment variables at run time (see .env.example);
# the image ships without a .env file.

ARG PHP_IMAGE=dunglas/frankenphp:1-php8.5-bookworm
ARG NODE_VERSION=24

FROM ${PHP_IMAGE} AS php-base

RUN install-php-extensions \
        bcmath \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        pgsql \
        redis \
        sockets \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Composer dependencies, without dev packages.
FROM php-base AS vendor

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && php artisan package:discover --ansi

# Front-end assets. The Wayfinder Vite plugin runs `php artisan`, so the
# build runs on the PHP stage with Node added.
FROM vendor AS assets

ARG NODE_VERSION

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates curl gnupg \
    && curl -fsSL "https://deb.nodesource.com/setup_${NODE_VERSION}.x" | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

RUN npm ci && npm run build

FROM php-base AS production

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    OCTANE_SERVER=frankenphp

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=vendor /app /app
COPY --from=assets /app/public/build /app/public/build

RUN rm -rf tests node_modules \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s CMD curl -fsS http://localhost:8000/up || exit 1

CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]
