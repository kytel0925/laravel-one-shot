# Tests (`tests/`)

Full strategy: [testing](../resources/docs/agents/testing.md).

Aggregate tests mirror `src/{Aggregate}/` inside each suite:

| Suite   | Location                                               | Boots app | Covers                                                  |
| ------- | ------------------------------------------------------ | --------- | ------------------------------------------------------- |
| Feature | `Feature/{Aggregate}/Features/{Action}Test.php`        | Yes       | Handlers through their endpoints                        |
| Unit    | `Unit/{Aggregate}/Models/`, `Unit/{Aggregate}/Common/` | No        | Models, enums, traits, and `Common/` helpers            |
| Browser | `Browser/{Aggregate}/`                                 | Yes       | The aggregate's user flows in real Chromium             |
| Feature | `Feature/` (top level, `Auth/`, `Settings/`)           | Yes       | Pages, authentication, settings, infrastructure, Octane |
| Browser | `Browser/` (top level)                                 | Yes       | Starter-kit flows: authentication, account, app shell   |
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
  database; the isolation of Redis, RabbitMQ, and cached configuration is in
  [testing](../resources/docs/agents/testing.md#database).
- `Feature/InfrastructureTest.php` proves the suite uses PostgreSQL, Redis,
  RabbitMQ, and Mailpit; keep it passing when drivers change.
- `Feature/OctaneRequestIsolationTest.php` shows how to drive requests through
  an Octane worker; extend it when a change touches request-scoped state.
- Use Pest syntax (`it()`/`test()`, `expect()`), matching sibling files.

## Browser tests

- Browser tests live in `Browser/` (starter-kit flows at the top level,
  aggregate flows in `Browser/{Aggregate}/`). They use the Pest browser plugin
  with the Chromium that Playwright installed in the app container
  (`PLAYWRIGHT_BROWSERS_PATH=/ms-playwright`).
- Open pages with `visit(route(...))`, select elements with `@data-test`
  attributes, and end every test with `assertNoJavaScriptErrors()`.
- The starter-kit flows cover sign in, password reset through the Mailpit link,
  the email verification link, the two-factor challenge, password
  confirmation and password change, account deletion, and the app shell.
  `Support/Mailpit.php` reads the mail the flows send.
- Failure screenshots land in `Browser/Screenshots`, which Git ignores.

## Commands

| Scope        | Command                                              |
| ------------ | ---------------------------------------------------- |
| One file     | `php artisan test --compact tests/Feature/XTest.php` |
| An aggregate | `php artisan test --compact tests/Feature/Tasks`     |
| One suite    | `php artisan test --compact --testsuite=Unit`        |
| Browser      | `php artisan test --compact --testsuite=Browser`     |
| Everything   | `php artisan test --compact`                         |
