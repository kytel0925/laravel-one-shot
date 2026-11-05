# Deployment

This document describes the environments the repository defines: the Docker Compose stack for
development and tests, the CI workflow, and the production image. Everything below was checked against
`docker-compose.yml`, `Dockerfile.dev`, `etc/`, `.env.example`, `.github/workflows/tests.yml` and the
`config/` files.

## Development and test topology (`docker-compose.yml`)

`test -f .env || cp .env.example .env; docker compose up -d` starts five services. Compose reads `.env` for the
`${...}` values; every variable has a default equal to `.env.example`, so the stack also starts before
`.env` exists.

| Service    | Image / build                  | Purpose                                                                                               | Published ports (host:container)                                   |
| ---------- | ------------------------------ | ----------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------ |
| `app`      | build of `Dockerfile.dev`      | Octane on FrankenPHP, Vite dev server, queue listener; also runs Composer, npm, Artisan and all tests | `APP_PORT` 8000:8000 (HTTP), `VITE_PORT` 5173:5173 (Vite)          |
| `postgres` | `postgres:18-alpine`           | Application database and the `testing` database                                                       | `FORWARD_DB_PORT` 5432:5432                                        |
| `redis`    | `redis:8-alpine`               | Sessions, cache, cache locks (`--appendonly yes`)                                                     | `FORWARD_REDIS_PORT` 6379:6379                                     |
| `rabbitmq` | `rabbitmq:4-management-alpine` | Queue backend, management UI                                                                          | `FORWARD_RABBITMQ_PORT` 5672:5672, `..._DASHBOARD_PORT` 15672      |
| `mailpit`  | `axllent/mailpit:v1`           | SMTP sink and web UI for outgoing mail                                                                | `FORWARD_MAILPIT_PORT` 1025:1025 (SMTP), `..._DASHBOARD_PORT` 8025 |

All published ports are bound to `FORWARD_BIND_ADDRESS` (default `127.0.0.1`, so they are reachable only
from the host). Set `FORWARD_BIND_ADDRESS=0.0.0.0` to expose them to other machines; the stack uses
default credentials (`guest`/`guest`, `laravel`/`secret`) and Redis without a password, so do this only on
a trusted network. Other services reach each other by service name (`postgres`, `redis`, `rabbitmq`,
`mailpit`) on the Compose network; the `.env.example` values already use those names. When PHP runs on
the host instead, use `127.0.0.1` and the `FORWARD_*` ports.

### The `app` image (`Dockerfile.dev`)

- Base `dunglas/frankenphp:1-php8.5-bookworm` (PHP 8.5, ZTS) with the extensions bcmath, intl, opcache,
  pcntl, pdo_pgsql, pgsql, redis, sockets and zip.
- Node 24 (`NODE_VERSION` build argument, from NodeSource), Composer 2, the PostgreSQL client, git, and
  Playwright's Chromium (`PLAYWRIGHT_VERSION` 1.64.1, installed to `/ms-playwright`; the build argument
  must match the `playwright` version in `package-lock.json`).
- The container runs as `${APP_UID:-1000}:${APP_GID:-1000}` (the host user), so files written into the bind
  mount (`vendor/`, `node_modules/`, `storage/`) stay editable on the host. `HOME`, Composer, npm,
  Caddy (`/data`, `/config`) and Playwright directories are world-writable for that reason. Set `APP_UID`
  and `APP_GID` to `id -u` and `id -g` if they are not 1000 (CI does this).
- The project is bind-mounted at `/app`; `vendor/bin` and `node_modules/.bin` are on `PATH`.
- `init: true`, `restart: unless-stopped`, `depends_on` with `condition: service_healthy` for postgres,
  redis and rabbitmq, and `service_started` for mailpit.

### Volumes

Named volumes `postgres-data` (`/var/lib/postgresql`), `redis-data` (`/data`) and `rabbitmq-data`
(`/var/lib/rabbitmq`) keep data across `docker compose down`; `docker compose down -v` deletes them.
Mailpit has no volume: captured mail is lost when the container is recreated.

### Health checks

| Service    | Check                                                                        | Timing                                                  |
| ---------- | ---------------------------------------------------------------------------- | ------------------------------------------------------- |
| `app`      | `curl -fsS http://localhost:8000/up`                                         | every 10 s, 5 s timeout, 60 retries, 600 s start period |
| `postgres` | `pg_isready -h 127.0.0.1 -U $POSTGRES_USER -d $POSTGRES_DB` (TCP on purpose) | every 5 s, 20 retries                                   |
| `redis`    | `redis-cli ping`                                                             | every 5 s, 20 retries                                   |
| `rabbitmq` | `rabbitmq-diagnostics -q ping`                                               | every 10 s, 20 retries                                  |
| `mailpit`  | none                                                                         | -                                                       |

The long `app` start period covers the first start, which installs Composer and npm dependencies.
`docker compose up -d --wait` returns when all checked services are healthy.

### Entry point (`etc/bin/dev.sh`)

`CMD` of `Dockerfile.dev`. In order, from `/app`:

1. Copies `.env.example` to `.env` when `.env` is missing.
2. Runs `composer install` when `vendor/autoload.php` is missing or `composer.lock` changed (stamp in
   `vendor/.composer-lock.sha1`), and `npm ci` when `node_modules` is missing or `package-lock.json`
   changed (stamp in `node_modules/.package-lock.sha1`).
3. Runs `php artisan key:generate` when `.env` has no `APP_KEY`.
4. Creates the test database (`DB_TEST_DATABASE`, default `testing`) when it does not exist. On a fresh
   PostgreSQL volume `etc/docker/postgres/create-testing-database.sh` (mounted into
   `/docker-entrypoint-initdb.d/`) already created it; this step covers volumes from before that script.
5. Runs `php artisan migrate --force` and `php artisan wayfinder:generate --with-form`.
6. Removes a stale `public/hot` and starts three processes:
    - `php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000 --workers=1 --max-requests=1`
      (a fresh worker per request, so code changes apply without a restart);
    - `npm run dev -- --host 0.0.0.0 --port 5173 --strictPort` (Vite with hot reload; `public/hot` exists
      only while it runs);
    - `php artisan queue:listen --tries=3 --sleep=3` (boots the application per job, so code changes apply).
7. Waits for the first process to exit, stops the others and exits with its status, so
   `restart: unless-stopped` restarts the whole set.

No scheduler runs in development, because the application defines no scheduled tasks.

## Configuration (`.env.example`)

The production image has no `.env`; every group below is read from process environment variables
(`config/*.php` `env()` calls). Defaults shown are `.env.example` values.

| Group                 | Variables                                                                                                                                                                                                               | What it drives                                                                                                                                                                                         |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Application           | `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`, `BCRYPT_ROUNDS`                                                                                                                                               | Name shown in the UI and mail, environment, encryption key (cookies, two-factor secrets), URL generation. `APP_URL` also fixes the passkey relying party id and allowed origin (`config/fortify.php`). |
| Locale                | `APP_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_FAKER_LOCALE`                                                                                                                                                                 | Translation and Faker locales                                                                                                                                                                          |
| Maintenance           | `APP_MAINTENANCE_DRIVER` (`file`), `APP_MAINTENANCE_STORE`                                                                                                                                                              | Where `artisan down` state is stored                                                                                                                                                                   |
| Logging               | `LOG_CHANNEL`, `LOG_STACK`, `LOG_DEPRECATIONS_CHANNEL`, `LOG_LEVEL`                                                                                                                                                     | Log channels and level                                                                                                                                                                                 |
| Database              | `DB_CONNECTION` (`pgsql`), `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_TEST_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`                                                                                                        | PostgreSQL connection; `DB_TEST_DATABASE` is only read by Compose and `dev.sh` to create the `testing` database; `phpunit.xml` pins the name `testing`                                                 |
| Session               | `SESSION_DRIVER` (`redis`), `SESSION_LIFETIME`, `SESSION_ENCRYPT`, `SESSION_PATH`, `SESSION_DOMAIN`                                                                                                                     | Session storage and cookie                                                                                                                                                                             |
| Cache                 | `CACHE_STORE` (`redis`), `CACHE_PREFIX`                                                                                                                                                                                 | Cache store (Redis database `REDIS_CACHE_DB`, default 1)                                                                                                                                               |
| Queue                 | `QUEUE_CONNECTION` (`rabbitmq`)                                                                                                                                                                                         | Default queue connection; failed jobs and batches use `DB_CONNECTION`                                                                                                                                  |
| Redis                 | `REDIS_CLIENT` (`phpredis`), `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT` (also read: `REDIS_DB` default 0, `REDIS_CACHE_DB`, `REDIS_PREFIX`)                                                                           | Sessions (database `REDIS_DB`) and cache                                                                                                                                                               |
| RabbitMQ              | `RABBITMQ_HOST`, `RABBITMQ_PORT`, `RABBITMQ_USER`, `RABBITMQ_PASSWORD`, `RABBITMQ_VHOST`, `RABBITMQ_QUEUE` (`default`)                                                                                                  | `vladimir-yuldashev/laravel-queue-rabbitmq` connection `rabbitmq` (`config/queue.php`); Compose also uses `RABBITMQ_USER`, `RABBITMQ_PASSWORD`, `RABBITMQ_VHOST` to create the broker user             |
| Mail                  | `MAIL_MAILER` (`smtp`), `MAIL_SCHEME`, `MAIL_HOST` (`mailpit`), `MAIL_PORT` (1025), `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`                                                             | Outgoing mail (verification and password reset emails)                                                                                                                                                 |
| Broadcast, filesystem | `BROADCAST_CONNECTION` (`log`), `FILESYSTEM_DISK` (`local`), `AWS_*`                                                                                                                                                    | The `AWS_*` values are only read by the `s3`/`sqs` configurations, which are not selected                                                                                                              |
| Front end             | `VITE_APP_NAME`, `VITE_PORT`, `VITE_HMR_HOST`                                                                                                                                                                           | Vite build and dev server (HMR host the browser connects to)                                                                                                                                           |
| Octane                | `OCTANE_SERVER` (`frankenphp`)                                                                                                                                                                                          | Octane server driver                                                                                                                                                                                   |
| Compose               | `APP_UID`, `APP_GID`, `FORWARD_BIND_ADDRESS`, `APP_PORT`, `FORWARD_DB_PORT`, `FORWARD_REDIS_PORT`, `FORWARD_RABBITMQ_PORT`, `FORWARD_RABBITMQ_DASHBOARD_PORT`, `FORWARD_MAILPIT_PORT`, `FORWARD_MAILPIT_DASHBOARD_PORT` | Read by `docker-compose.yml` only: container user and published ports                                                                                                                                  |

`PHP_CLI_SERVER_WORKERS` is present but commented out in `.env.example`.

Behavior that depends on the environment: in production (`APP_ENV=production`)
`App\Providers\AppServiceProvider` makes `Password::defaults()` require 12 characters with mixed case,
letters, numbers, symbols and an uncompromised check (outside production the default 8-character rule
applies), and prohibits destructive database commands (`migrate:fresh`, `db:wipe`, ...).

### Tests

`phpunit.xml` forces: `DB_DATABASE=testing`, `REDIS_DB=2`, `REDIS_CACHE_DB=3`, `REDIS_PREFIX=testing-`,
`CACHE_STORE=redis`, `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=rabbitmq` with `RABBITMQ_QUEUE=testing`,
`MAIL_MAILER=smtp` (to Mailpit) and `BROADCAST_CONNECTION=null`. Connection hosts and credentials come from
`.env`, so the same file works in the container and in CI. See
`resources/docs/adr/20261104-tests-against-docker-services.md`.

## CI (`.github/workflows/tests.yml`)

Workflow `tests`, triggered on pushes to `master` and on pull requests; permissions `contents: read`;
one run per ref (`cancel-in-progress`); a single job `ci` on `ubuntu-latest` with a 45-minute timeout.
Steps:

1. Check out the code (`actions/checkout`, pinned by commit, no persisted credentials).
2. Prepare `.env`: copy `.env.example` and set `APP_UID` and `APP_GID` to the runner's user.
3. `docker compose up -d --build --wait --wait-timeout 1200`: builds `Dockerfile.dev` and starts the same
   five services as development, waiting for the health checks.
4. `docker compose exec -T app npm run build`: builds the front-end assets.
5. `docker compose exec -T app composer ci:check`: `npm run check` (`vp check`), `npm run types:check`
   (`vue-tsc`), `npm run test` (frontend unit tests), then `composer test` (clears the config cache, Pint in
   test mode, Larastan/PHPStan, `php artisan test` for the Unit, Feature and Browser suites).
6. On failure only: prints the last 300 log lines per container and uploads
   `tests/Browser/Screenshots` as the `browser-screenshots` artifact.

## Production image (`etc/docker/Dockerfile`)

Build from the repository root:

```bash
docker build -f etc/docker/Dockerfile -t laravel-one-shot .
```

Build arguments: `PHP_IMAGE` (default `dunglas/frankenphp:1-php8.5-bookworm`) and `NODE_VERSION` (24).
The build context honors `.dockerignore` (excludes `.env*` except `.env.example`, `vendor`,
`node_modules`, `public/build`, `public/hot`, local state and agent folders).

| Stage        | Based on       | What it does                                                                                                                                                                                                                |
| ------------ | -------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `php-base`   | `${PHP_IMAGE}` | Installs the same PHP extensions as the dev image and Composer.                                                                                                                                                             |
| `vendor`     | `php-base`     | `composer install --no-dev` (cached on `composer.json`/`composer.lock`), copies the source, dumps an optimized authoritative classmap, runs `package:discover`, and copies Octane's `frankenphp-worker.php` into `public/`. |
| `assets`     | `vendor`       | Adds Node, runs `npm ci && npm run build` (on the PHP stage because the Wayfinder Vite plugin runs `php artisan`). Only `public/build` is taken from this stage.                                                            |
| `production` | `php-base`     | Copies `/app` from `vendor` and `public/build` from `assets`, uses `php.ini-production`, removes `tests/` and `node_modules/`, prepares writable `storage/`, `bootstrap/cache` and the Caddy directories for `www-data`.    |

Runtime properties of the final image:

- Environment: `APP_ENV=production`, `APP_DEBUG=false`, `LOG_CHANNEL=stderr`, `OCTANE_SERVER=frankenphp`.
- Runs as `www-data`; exposes port 8000 (plain HTTP).
- `HEALTHCHECK`: `curl -fsS http://localhost:8000/up` every 30 s (5 s timeout, 20 s start period).
- Default command: `php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000` (Octane defaults:
  `--workers=auto`, `--max-requests=500`).
- No `.env` file: configuration is read from the process environment only.

### What must be provided at run time

- `APP_KEY` (for example `php artisan key:generate --show`), `APP_URL` (the public URL, used for URL
  generation, signed email-verification links and as the passkey relying party and allowed origin),
  `APP_NAME`.
- Database: `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. The
  database must exist; the image does not create it.
- Redis: `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD` (and `REDIS_CLIENT=phpredis`, the PHP extension is
  installed). Sessions use database `REDIS_DB` (0) and the cache `REDIS_CACHE_DB` (1).
- RabbitMQ: `RABBITMQ_HOST`, `RABBITMQ_PORT`, `RABBITMQ_USER`, `RABBITMQ_PASSWORD`, `RABBITMQ_VHOST`,
  `RABBITMQ_QUEUE`.
- Mail: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`,
  `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`. The code default for `MAIL_MAILER` is `log`; set an SMTP (or other)
  mailer or verification and reset emails are only written to the log.
- Keep `SESSION_DRIVER=redis`, `CACHE_STORE=redis` and `QUEUE_CONNECTION=rabbitmq` (their defaults in
  `config/`) unless you also provide the `sessions`/`cache`/`jobs` tables, which exist in the schema
  (see `ER.md`).

### Processes

The image starts only the web process. Run the others as separate containers from the same image with a
different command:

| Process      | Command                           | Notes                                                                                                                                    |
| ------------ | --------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| Web          | image default (`octane:start`)    | Scale horizontally behind a load balancer.                                                                                               |
| Queue worker | `php artisan queue:work rabbitmq` | Add `--tries`, `--timeout` and `--max-time` as needed; restart it on every deploy. Failed jobs are stored in PostgreSQL (`failed_jobs`). |
| Scheduler    | `php artisan schedule:work`       | Run exactly one instance. The application currently defines no scheduled tasks, so it is optional until one is added.                    |

### Release steps

1. Build and push the image.
2. Run `php artisan migrate --force` once per release, from a one-off container of the new image with the
   production environment, before starting the new web and worker containers. (Destructive commands such as
   `migrate:fresh` are blocked in production.)
3. Start or roll the web, queue worker and scheduler containers; send `SIGTERM` to the old workers so they
   finish the current job.

The image does not run `config:cache`, `route:cache`, `view:cache` or `event:cache`; running them in the
release step is optional.

## Known limits

- Plain HTTP only: Octane/FrankenPHP in the image listens on port 8000 without TLS, so terminate TLS in a
  reverse proxy or load balancer. `bootstrap/app.php` does not configure trusted proxies, so Laravel does not
  honor `X-Forwarded-*` headers; behind a TLS proxy it generates `http://` URLs from the proxied request.
  Configure trusted proxies before relying on signed URLs or secure cookies behind a proxy.
- Server-side rendering: `config/inertia.php` enables Inertia SSR. In development, while `public/hot`
  exists, Inertia posts the page to the Vite dev server (`/__inertia_ssr`). In production it posts to
  `http://127.0.0.1:13714/render`, but the production image does not build the SSR bundle
  (`npm run build:ssr`) and starts no SSR server. Inertia catches the failed request (connection error)
  and the browser renders the page client-side. Run an SSR process yourself if you need server-rendered HTML in production.
- The development stack is not hardened: default credentials, no TLS, `APP_DEBUG=true`, a fresh worker
  per request (`--max-requests=1`), `restart: unless-stopped` without resource limits.
- `FILESYSTEM_DISK=local` writes to the container filesystem (`storage/app`), which is ephemeral; the
  application stores no uploaded files today. Mount a volume or configure an object store before adding
  uploads.
- Mailpit data and the Compose volumes are for development only; there is no backup procedure for them
  in this repository.
- RabbitMQ, Redis and PostgreSQL are expected as managed or separately operated services in production;
  the repository defines no production Compose or orchestration manifests.
