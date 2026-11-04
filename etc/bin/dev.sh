#!/usr/bin/env bash
# Entry point of the `app` container (Dockerfile.dev). Installs dependencies,
# prepares .env and the database, then runs Octane on FrankenPHP, the Vite dev
# server and a RabbitMQ queue worker until one of them exits.
set -euo pipefail

cd /app

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ ! -f vendor/autoload.php ] || [ composer.lock -nt vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if ! grep -qE '^APP_KEY=.+' .env; then
    php artisan key:generate --no-interaction --force
fi

if [ ! -d node_modules ] || [ package-lock.json -nt node_modules/.package-lock.json ]; then
    npm ci
fi

php artisan migrate --force --no-interaction
php artisan wayfinder:generate --with-form --no-interaction

# Remove a stale Vite marker so Laravel serves the dev server only while it runs.
rm -f public/hot

pids=()

php artisan octane:start \
    --server=frankenphp \
    --host=0.0.0.0 \
    --port=8000 \
    --workers=1 \
    --max-requests=1 &
pids+=($!)

npm run dev -- --host 0.0.0.0 --port 5173 --strictPort &
pids+=($!)

php artisan queue:work --tries=3 --sleep=3 &
pids+=($!)

trap 'kill "${pids[@]}" 2>/dev/null || true' TERM INT

# Stop the container when any process exits, so `restart: unless-stopped`
# brings the whole set back.
wait -n "${pids[@]}"
status=$?
kill "${pids[@]}" 2>/dev/null || true
exit "$status"
