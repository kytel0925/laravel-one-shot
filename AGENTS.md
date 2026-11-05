# Agent instructions

Laravel One-Shot: a template on Laravel 13 / PHP 8.5 with Inertia 3, Vue 3,
TypeScript and Vuetify 4 (Material Design 3), served by Octane on FrankenPHP
over PostgreSQL, with Redis (cache, sessions) and RabbitMQ (queue). The whole
stack runs in Docker Compose.

Package managers: Composer and npm (`package-lock.json`; restore with `npm ci`).

## Commands

Every command except the first runs inside the `app` container: prefix it with
`docker compose exec app` (add `-T` when there is no terminal).

| Task                        | Command                                                                        |
| --------------------------- | ------------------------------------------------------------------------------ |
| Start the environment       | `test -f .env \|\| cp .env.example .env; docker compose up -d` (from the host) |
| Format PHP after edits      | `vendor/bin/pint --dirty --format agent`                                       |
| PHP tests                   | `php artisan test --compact [path]`                                            |
| Browser tests               | `php artisan test --compact --testsuite=Browser`                               |
| Static analysis (Larastan)  | `composer types:check`                                                         |
| Frontend lint and format    | `npm run check`                                                                |
| Frontend types              | `npm run types:check`                                                          |
| Frontend unit tests         | `npm run test`                                                                 |
| Build and regenerate routes | `npm run build`                                                                |
| Full CI checks (ask first)  | `composer ci:check`                                                            |

## Every task

1. The code wins over these documents. When they disagree, follow the code
   and report the difference.
2. Match sibling files. Stay within the task. Ask before adding a base folder
   or a dependency.
3. After PHP edits, run Pint, the affected tests, and `composer types:check`.
   Run frontend checks after frontend edits. Ask before running the full suite
   or `composer ci:check`: they are slow and include the browser tests.
4. Update `resources/docs/` in the same change when routes, requests,
   responses, domain events, migrations, or deployment change (see
   [resources/docs/AGENTS.md](resources/docs/AGENTS.md)).
5. Report what changed, what was verified, and any limitations.

## Read when relevant

| When you are                                           | Read                                                                           |
| ------------------------------------------------------ | ------------------------------------------------------------------------------ |
| Adding or changing aggregates, slices, or routes       | [resources/docs/agents/architecture.md](resources/docs/agents/architecture.md) |
| Writing PHP: models, validation, errors, Octane        | [resources/docs/agents/laravel-php.md](resources/docs/agents/laravel-php.md)   |
| Changing pages, components, or Vuetify UI              | [resources/docs/agents/frontend.md](resources/docs/agents/frontend.md)         |
| Writing or running tests                               | [resources/docs/agents/testing.md](resources/docs/agents/testing.md)           |
| Committing, or creating GitHub issues or pull requests | [resources/docs/agents/git.md](resources/docs/agents/git.md)                   |
| Running the app, Docker, CI, or changing tool versions | [resources/docs/agents/environment.md](resources/docs/agents/environment.md)   |

Directories with their own `AGENTS.md` (`src/`, `app/`, `tests/`,
`resources/docs/`, `resources/js/`) add rules for that directory; Claude Code
does not load them automatically, so open them before editing there.
Precedence: your instructions, then the nested `AGENTS.md`, then
`resources/docs/agents/`, then the generated
[Laravel Boost guidelines](resources/docs/agents/laravel-boost.md).

When changing agent instructions, keep this file short and put each rule in
exactly one file under `resources/docs/agents/` or a nested `AGENTS.md`.
`CLAUDE.md` only imports this file; never copy rules into it, `.claude/`, or
`.codex/`. `composer update` regenerates `resources/docs/agents/laravel-boost.md`
(`boost:update`), so override its rules in `resources/docs/agents/` instead of
editing it.
