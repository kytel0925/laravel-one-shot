# Environment

## Development

- `Dockerfile.dev`, `docker-compose.yml`, and `etc/bin/dev.sh` define the
  environment. Compose runs the `app` container beside PostgreSQL, Redis,
  RabbitMQ, and Mailpit. `dev.sh` installs Composer and npm dependencies when
  their lock file hashes change, generates `APP_KEY`, creates the `testing`
  database, migrates, generates the Wayfinder helpers, and starts Octane on
  FrankenPHP (`--workers=1 --max-requests=1`), the Vite dev server, and
  `queue:listen`. Check for running servers before starting another.
- Start the stack from the host with `cp -n .env.example .env && docker compose up -d`.
  Run every other command through `docker compose exec app ...`. Never
  overwrite an existing `.env`: `dev.sh` generates `APP_KEY` into it once, and
  a regenerated key makes Fortify's encrypted two-factor secrets and recovery
  codes unreadable and ends every session. `cp -n` copies only when `.env` is
  missing.
- The `app` container runs as `APP_UID`/`APP_GID` (default 1000), so files it
  writes stay editable on the host. Published ports bind to
  `FORWARD_BIND_ADDRESS` (default `127.0.0.1`).
- `composer dev` (from the host: it calls `docker compose`) brings the stack
  up and follows the `app` logs; `npm run dev` starts Vite Plus.
- `composer setup` (from the host, for the same reason) copies `.env` when it
  is missing and runs `docker compose up -d --wait`. It changes state, so never
  use it to verify a change.
- `.env.example` holds configuration names and defaults. Read resolved values
  with `php artisan config:show`, not the `config/` defaults, and do not print
  secrets.
- Sessions and cache use Redis, the queue uses RabbitMQ, and mail goes to
  Mailpit (web UI on port 8025). The RabbitMQ management UI is on port 15672.

## Versions

- Check installed versions before using a package API: `composer show
  vendor/package` and `npm ls package --depth=0`. Manifest ranges are not
  installed versions.
- PHP 8.5 (FrankenPHP image) in `Dockerfile.dev` and `etc/docker/Dockerfile`.
  Do not change it silently.
- Node 24 in both images. The `NODE_VERSION` argument in `Dockerfile.dev` and
  `etc/docker/Dockerfile` must match. Update them together.
- `PLAYWRIGHT_VERSION` in `Dockerfile.dev` must match `playwright` in
  `package-lock.json`. Update them together.
- Browser tests use the Chromium installed in `/ms-playwright`.
- The production image is built from `etc/docker/Dockerfile`; CI runs on
  GitHub Actions (`.github/workflows/tests.yml`) with the same Compose stack.
