# Tests (`tests/`)

Full strategy: [testing](../resources/docs/agents/testing.md).

Aggregate tests mirror `src/{Aggregate}/` inside each suite:

| Suite   | Location                                               | Boots app | Covers                                                  |
| ------- | ------------------------------------------------------ | --------- | ------------------------------------------------------- |
| Feature | `Feature/{Aggregate}/Features/{Action}Test.php`        | Yes       | Handlers through their endpoints                        |
| Unit    | `Unit/{Aggregate}/Models/`, `Unit/{Aggregate}/Common/` | No        | Models, enums, traits, and `Common/` helpers            |
| Browser | `Browser/{Aggregate}/`                                 | Yes       | The aggregate's user flows in real Chromium             |
| Feature | `Feature/` (top level, `Auth/`, `Settings/`)           | Yes       | Pages, authentication, settings, infrastructure, Octane |
| Browser | `Browser/` (top level)                                 | Yes       | Starter-kit flows: sign in, app shell                   |
| Unit    | `Unit/` (top level, `Docs/`)                           | No        | `app/Support`, shared traits, documentation rules       |

- Handlers are the primary unit under test. For each feature cover success and
  database state, validation (422), guests (401 or redirect), other owners
  (404), and no writes on failure.
- Build every URL with `route()` and the named route, passing parameters
  explicitly. Never hard-code paths.
- Use factories and their states (`unverified()`, `withTwoFactor()`).
- `Pest.php` applies `Tests\TestCase` and `RefreshDatabase` to `Feature` and
  `Browser`, including aggregate subfolders. `phpunit.xml` points them at the
  separate `testing` PostgreSQL database, so a run never touches the `.env`
  database.
- `Feature/InfrastructureTest.php` proves the suite uses PostgreSQL, Redis,
  RabbitMQ, and Mailpit; keep it passing when drivers change.
- `Feature/OctaneRequestIsolationTest.php` shows how to drive requests through
  an Octane worker; extend it when a change touches request-scoped state.
- Browser tests use `visit(route(...))`, select with `@data-test`, and end with
  `assertNoJavaScriptErrors()`.
- Use Pest syntax (`it()`/`test()`, `expect()`), matching sibling files.

| Scope        | Command                                              |
| ------------ | ---------------------------------------------------- |
| One file     | `php artisan test --compact tests/Feature/XTest.php` |
| An aggregate | `php artisan test --compact tests/Feature/Tasks`     |
| One suite    | `php artisan test --compact --testsuite=Unit`        |
| Browser      | `php artisan test --compact --testsuite=Browser`     |
| Everything   | `php artisan test --compact`                         |
