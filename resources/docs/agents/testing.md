# Testing

Suite layout and the per-feature coverage list are in
[tests/AGENTS.md](../../../tests/AGENTS.md).

## Strategy

| Layer                        | Location                                              | What to cover                                                                                           |
| ---------------------------- | ----------------------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| Handler (via endpoint)       | `tests/Feature/{Aggregate}/Features/{Action}Test.php` | Success result and database state, validation, 401/redirect, 404 for other owners, no writes on failure |
| Models, enums, `Common/`     | `tests/Unit/{Aggregate}/Models/`, `Common/`           | Pure behavior without the application booted                                                            |
| Aggregate user flows         | `tests/Browser/{Aggregate}/`                          | Real Chromium flows; finish with `assertNoJavaScriptErrors()`                                           |
| Starter-kit user flows       | `tests/Browser/`                                      | Sign in, app shell                                                                                      |
| Inertia pages outside `src/` | `tests/Feature/`                                      | Access rules and `assertInertia` component and props                                                    |
| `app/Support`, shared traits | `tests/Unit/`                                         | Pure behavior without the application booted                                                            |
| Documentation rules          | `tests/Unit/Docs/AsyncApiScopeTest.php`               | AsyncAPI documents domain events only (see below)                                                       |
| Infrastructure               | `tests/Feature/InfrastructureTest.php`                | The suite uses PostgreSQL, Redis, RabbitMQ, and Mailpit                                                 |
| Octane request isolation     | `tests/Feature/OctaneRequestIsolationTest.php`        | Users and inputs do not leak between requests on one worker                                             |

- Test Handlers through their endpoints, so routing, middleware, validation,
  and authorization run as in production.
- Behavior changes need regression tests. Copy, styling, and layout-only
  changes do not.
- Create tests with `php artisan make:test --pest {Aggregate}/Features/{Action}Test`
  (add `--unit` for Unit); the suite folder is not part of the name.

## Documentation tests

`tests/Unit/Docs/AsyncApiScopeTest.php` enforces the
[AsyncAPI scope](../AGENTS.md#asyncapi-scope): every channel address in
`resources/docs/api/*.asyncapi.yaml` must be a class implementing
`App\Support\DomainEvent`, and every `DomainEvent` class under `src/` must be
documented. When it fails, fix the spec or the event, never the test.

## Browser tests

- Browser tests live in `tests/Browser/` (starter-kit flows at the top level,
  aggregate flows in `tests/Browser/{Aggregate}/`). They use the Pest browser
  plugin with the Chromium that Playwright installed in the app container
  (`PLAYWRIGHT_BROWSERS_PATH=/ms-playwright`).
- Open pages with `visit(route(...))`, select elements with `@data-test`
  attributes, and end every test with `assertNoJavaScriptErrors()`.
- Failure screenshots land in `tests/Browser/Screenshots`, which Git ignores.

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
- Redis for cache and sessions (databases 2 and 3, prefix `testing-`).
- RabbitMQ for the queue, on the `testing` queue.
- Mailpit over SMTP.

Host names and credentials come from `.env`, so the same `phpunit.xml` works
in the `app` container and in CI. `RefreshDatabase` resets only `testing`; the
development database is never touched.

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
