# Tests run against the Docker Compose services

Status: accepted
Date: 2026-11-04

## Context

Tests that run on SQLite, array cache, array sessions or a sync queue pass on behavior that production does
not have: different SQL dialect and constraint handling, no serialization of cached values or jobs, no
broker. Bugs then surface only after deployment. The application uses PostgreSQL, Redis and RabbitMQ in every
environment, and the Compose stack (see `20261104-docker-compose-environment.md`) already provides them.

## Decision

All test suites (Unit, Feature, Browser) run against the real services of the Compose stack; SQLite and
`array` drivers are not used. `phpunit.xml` forces the values:

- Database: PostgreSQL, a separate `testing` database (`DB_DATABASE=testing`) created by
  `etc/docker/postgres/create-testing-database.sh` and, for older volumes, by `etc/bin/dev.sh`, so running
  tests never resets development data.
- Redis: sessions and cache on Redis, database 2 (`REDIS_DB`) and 3 (`REDIS_CACHE_DB`) with the key prefix
  `testing-`, apart from development's databases 0 and 1.
- Queue: RabbitMQ with the dedicated queue `testing` (`RABBITMQ_QUEUE`).
- Mail: the SMTP transport to Mailpit; broadcasting is `null`.
- Connection hosts and credentials come from `.env`, so the same configuration works in the `app` container
  and in CI.
- Browser tests use Pest's browser plugin with Playwright's Chromium, installed in the `app` image, against
  the running application.
- CI (`.github/workflows/tests.yml`) starts the same Compose stack and runs `composer ci:check` inside the
  `app` container, so CI and local runs are identical.

## Consequences

- Tests exercise the real PostgreSQL schema and constraints, Redis serialization and RabbitMQ delivery, so
  production-only failures show up earlier.
- Tests need the stack running; they do not run without Docker, and they are slower than in-memory tests.
- Tests share services with development, isolated by database, Redis database numbers, key prefix and queue
  name. Anything outside those (for example the RabbitMQ virtual host and Mailpit's inbox) is shared and tests
  must clean up or tolerate other messages.
- Code that must be tested with fakes (`Mail::fake()`, `Queue::fake()`, `Event::fake()`) still can; the
  default is real infrastructure.
- Browser tests add Chromium to the `app` image and make CI slower.
