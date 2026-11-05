# Testing

Suite layout and the per-feature coverage list are in
[tests/AGENTS.md](../../../tests/AGENTS.md).

## Strategy

| Layer                        | Location                                              | What to cover                                                                                                                    |
| ---------------------------- | ----------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| Handler (via endpoint)       | `tests/Feature/{Aggregate}/Features/{Action}Test.php` | Success result and database state, validation, 401/redirect, 404 for other owners, no writes on failure                          |
| Models, enums, `Common/`     | `tests/Unit/{Aggregate}/Models/`, `Common/`           | Pure behavior without the application booted                                                                                     |
| Aggregate user flows         | `tests/Browser/{Aggregate}/`                          | Real Chromium flows; finish with `assertNoJavaScriptErrors()`                                                                    |
| Starter-kit user flows       | `tests/Browser/`                                      | Sign in, password reset, email verification, two-factor challenge, password confirmation and change, account deletion, app shell |
| Inertia pages outside `src/` | `tests/Feature/`                                      | Access rules and `assertInertia` component and props                                                                             |
| `app/Support`, shared traits | `tests/Unit/`                                         | Pure behavior without the application booted                                                                                     |
| Documentation rules          | `tests/Unit/Docs/AsyncApiScopeTest.php`               | AsyncAPI documents domain events only (see below)                                                                                |
| Infrastructure               | `tests/Feature/InfrastructureTest.php`                | The suite uses PostgreSQL, Redis, RabbitMQ, and Mailpit                                                                          |
| Octane request isolation     | `tests/Feature/OctaneRequestIsolationTest.php`        | Users and inputs do not leak between requests on one worker                                                                      |

- Test Handlers through their endpoints, so routing, middleware, validation,
  and authorization run as in production.
- Behavior changes need regression tests. Copy, styling, and layout-only
  changes do not.
- Create tests with `php artisan make:test --pest {Aggregate}/Features/{Action}Test`
  (add `--unit` for Unit); the suite folder is not part of the name.

## Documentation tests

`tests/Unit/Docs/AsyncApiScopeTest.php` enforces the
[AsyncAPI scope](../AGENTS.md#asyncapi-scope), where its checks are listed.
When it fails, fix the spec or the event, never the test.

## Browser tests

The rules for writing browser tests are in
[tests/AGENTS.md](../../../tests/AGENTS.md#browser-tests).

## Frontend unit tests

Pure TypeScript helpers in `resources/js/lib/` get a colocated `*.test.ts`
(`utils.test.ts` next to `utils.ts`) that imports `describe`, `it`, and
`expect` from `vite-plus/test`. Run them with `npm run test` (`vp test`;
`test.include` in `vite.config.ts` limits it to `resources/js/**/*.test.ts`, so
the specs under `vendor/` are not collected). Components and pages are covered
by browser tests instead.

## Database

Tests never use SQLite or `array` drivers. `phpunit.xml` forces the Docker
Compose services:

- PostgreSQL: `DB_CONNECTION=pgsql` with `DB_DATABASE=testing`, a separate
  database created by `etc/docker/postgres/create-testing-database.sh` and
  `etc/bin/dev.sh`.
- Redis for cache and sessions (databases 2 and 3, prefix `testing-`). Both
  databases are flushed before each test that boots the application
  (`tests/TestCase.php`).
- RabbitMQ for the queue, on the `testing` queue, which `tests/TestCase.php`
  purges once per run; `InfrastructureTest` sends a real job through it and
  runs `queue:work --once`. Tests share RabbitMQ's `/` vhost with development,
  so never dispatch test jobs onto another queue name.
- Mailpit over SMTP. `tests/Support/Mailpit.php` reads its messages over the
  HTTP API, so tests follow the real reset and verification links.

Host names and credentials come from `.env`, so the same `phpunit.xml` works
in the `app` container and in CI. `RefreshDatabase` resets only `testing`; the
development database is never touched.

A cached configuration must never point the tests at the development
services. `phpunit.xml` forces `APP_CONFIG_CACHE`, `APP_ROUTES_CACHE`, and
`APP_EVENTS_CACHE` to paths nothing writes, and forces `REDIS_URL` empty so it
cannot override the Redis database above. `tests/TestCase.php` refuses to run
unless the connection really is the `testing` database and Redis DB 2.

CI (`.github/workflows/tests.yml`) starts the Compose stack and runs
`composer ci:check` on `master` and on pull requests.

## Running

| Scope                  | Command                                              |
| ---------------------- | ---------------------------------------------------- |
| One file               | `php artisan test --compact tests/Feature/XTest.php` |
| By name                | `php artisan test --compact --filter=testName`       |
| An aggregate           | `php artisan test --compact tests/Feature/Tasks`     |
| One suite              | `php artisan test --compact --testsuite=Unit`        |
| Browser suite          | `php artisan test --compact --testsuite=Browser`     |
| Frontend unit tests    | `npm run test`                                       |
| PHP checks (ask first) | `composer test`                                      |
| Full CI (ask first)    | `composer ci:check`                                  |

Run every command inside the app container (`docker compose exec app ...`).
Run the narrowest set that covers the change, and rerun it after each fix.
Ask before running the whole suite: it is slow and includes the browser tests.
`composer test` clears cached configuration, then runs Pint in test mode,
Larastan, and all tests; `composer ci:check` adds the frontend checks first.
