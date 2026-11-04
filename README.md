# Laravel One-Shot

A production-ready template built on the Laravel 13 Inertia + Vue starter kit
(no teams). It runs Vue 3, TypeScript and Inertia 3 with Vuetify (Material
Design), served by Octane on FrankenPHP (PHP 8.5). PostgreSQL, Redis,
RabbitMQ and Mailpit run in Docker Compose.

## Quick start

```bash
cp .env.example .env && docker compose up -d
```

The first start installs Composer and npm dependencies, generates `APP_KEY`,
runs the migrations, and then starts Octane, the Vite dev server and a queue
worker. Watch the progress with `docker compose logs -f app`.

| Service           | URL / port                                 |
| ----------------- | ------------------------------------------ |
| Application       | http://localhost:8000                      |
| Vite dev server   | http://localhost:5173                      |
| Mailpit (web UI)  | http://localhost:8025                      |
| RabbitMQ (web UI) | http://localhost:15672 (`guest` / `guest`) |
| PostgreSQL        | `localhost:5432` (`laravel` / `secret`)    |
| Redis             | `localhost:6379`                           |

Each published port can be changed in `.env` (`APP_PORT`, `FORWARD_DB_PORT`, …).

## Everyday commands

Run them inside the `app` container:

```bash
docker compose exec app php artisan test --compact
```

```bash
docker compose exec app composer ci:check
```

```bash
docker compose exec app npm run build
```

Tests use the Compose services, not SQLite or array drivers: the `testing`
PostgreSQL database (created by `etc/docker/postgres/`), Redis, RabbitMQ and
Mailpit. Browser tests (Pest + Playwright Chromium) run in the same container.

## Project documentation

- [AGENTS.md](AGENTS.md): instructions for coding agents, and the index of the
  project rules in `resources/docs/agents/`.
- [resources/docs/](resources/docs/): ADRs, OpenAPI and AsyncAPI specs,
  `DEPLOYMENT.md` and `ER.md`.

## License

MIT.
