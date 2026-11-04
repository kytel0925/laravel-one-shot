# Docker Compose as the development and test environment

Status: accepted
Date: 2026-11-04

## Context

The application depends on PostgreSQL, Redis, RabbitMQ and an SMTP server, runs on FrankenPHP with Octane,
and needs Node and a browser for the front end and the browser tests. Installing and versioning all of that
on every developer machine and on CI differs from machine to machine, and "works on my machine" setups hide
differences from production (SQLite instead of PostgreSQL, sync queues, array caches). We want a new
checkout to run with no manual setup.

## Decision

Docker Compose is the one supported development and test environment (`docker-compose.yml`):

- Start with `test -f .env || cp .env.example .env; docker compose up -d`. Every `${...}` variable has a default equal to
  `.env.example`, so the stack also starts before `.env` exists.
- Services: `app` (build of `Dockerfile.dev`: PHP 8.5 on FrankenPHP, Composer, Node 24, Playwright's
  Chromium), `postgres:18-alpine`, `redis:8-alpine`, `rabbitmq:4-management-alpine` and
  `axllent/mailpit:v1`. Health checks gate `app` on PostgreSQL, Redis and RabbitMQ.
- `etc/bin/dev.sh` is the `app` entry point: it installs dependencies when the lock files change, generates
  `APP_KEY`, migrates, and runs Octane on FrankenPHP, the Vite dev server and a queue listener.
- Octane runs with `--workers=1 --max-requests=1` in development: every request boots a fresh worker, so code
  changes apply without a restart (the queue uses `queue:listen` for the same reason). The cost is slower
  requests than a warm worker; production uses Octane's defaults.
- The container runs as the host user (`APP_UID`, `APP_GID`, default 1000:1000) so files written into the bind
  mount (`vendor/`, `node_modules/`, `storage/`) stay editable on the host. Set the variables to `id -u` and
  `id -g` on Linux when they differ.
- Published ports bind to `127.0.0.1` (`FORWARD_BIND_ADDRESS`), because the stack uses default credentials.
  Set `FORWARD_BIND_ADDRESS=0.0.0.0` deliberately to expose it.
- Commands run inside the container: `docker compose exec app ...`.
- The production image is separate (`etc/docker/Dockerfile`); see `DEPLOYMENT.md`.

## Consequences

- One command gives every developer, agent and CI run the same PHP, Node, database and broker versions.
- The first start is slow (image build, Composer and npm installs; the health check allows 600 seconds).
- Docker is a hard requirement; running PHP on the host is possible only through the `FORWARD_*` ports and
  is unsupported.
- Bind-mounted `vendor/` and `node_modules/` are built for the container's platform, not the host's.
- Version bumps of PHP, Node, PostgreSQL, Redis, RabbitMQ and Mailpit happen in one place and are exercised by
  CI before they reach anyone.
- The development stack is not hardened (default credentials, debug on) and must never be exposed publicly.
