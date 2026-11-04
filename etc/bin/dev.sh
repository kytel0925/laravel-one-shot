#!/usr/bin/env bash
# Entry point of the `app` container (Dockerfile.dev). Installs dependencies,
# prepares .env and the databases, then runs Octane on FrankenPHP, the Vite
# dev server and a RabbitMQ queue listener until one of them exits.
set -euo pipefail

pids=()
trap 'kill "${pids[@]}" 2>/dev/null || true; exit 143' TERM INT

cd /app

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Install only when the lock files changed since the last install.
stamp() { sha1sum "$1" | cut -d' ' -f1; }

if [ ! -f vendor/autoload.php ] || [ "$(stamp composer.lock)" != "$(cat vendor/.composer-lock.sha1 2>/dev/null)" ]; then
    composer install --no-interaction --prefer-dist
    stamp composer.lock > vendor/.composer-lock.sha1
fi

if [ ! -d node_modules ] || [ "$(stamp package-lock.json)" != "$(cat node_modules/.package-lock.sha1 2>/dev/null)" ]; then
    npm ci
    stamp package-lock.json > node_modules/.package-lock.sha1
fi

if ! grep -qE '^APP_KEY=.+' .env; then
    php artisan key:generate --no-interaction --force
fi

# The testing database is created by etc/docker/postgres on a fresh volume;
# create it here too for volumes that existed before that script.
php -r '
    require "vendor/autoload.php";
    $app = require "bootstrap/app.php";
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $name = env("DB_TEST_DATABASE", "testing");
    $db = Illuminate\Support\Facades\DB::connection();
    if ($db->getDriverName() === "pgsql" && ! $db->scalar("SELECT 1 FROM pg_database WHERE datname = ?", [$name])) {
        $db->statement("CREATE DATABASE ".$db->getQueryGrammar()->wrap($name));
        echo "Created database {$name}\n";
    }
'

php artisan migrate --force --no-interaction
php artisan wayfinder:generate --with-form --no-interaction

# Remove a stale Vite marker so Laravel serves the dev server only while it runs.
rm -f public/hot

php artisan octane:start \
    --server=frankenphp \
    --host=0.0.0.0 \
    --port=8000 \
    --workers=1 \
    --max-requests=1 &
pids+=($!)

npm run dev -- --host 0.0.0.0 --port 5173 --strictPort &
pids+=($!)

# queue:listen boots a fresh application per job, so code changes apply
# without a restart (like --max-requests=1 for HTTP).
php artisan queue:listen --tries=3 --sleep=3 &
pids+=($!)

# Stop the container when any process exits, so `restart: unless-stopped`
# brings the whole set back.
status=0
wait -n "${pids[@]}" || status=$?
kill "${pids[@]}" 2>/dev/null || true
exit "$status"
