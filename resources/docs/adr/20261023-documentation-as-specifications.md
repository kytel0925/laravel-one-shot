# Documentation as specifications

Status: accepted
Date: 2026-10-23

## Context

Behavior, contracts and operating knowledge of the application used to live in people's heads, in pull
request threads and in comments that drift away from the code. Coding agents and new team members need a
single place to learn what the system does and how it is run, and that place must stay true when the code
changes. Documentation that is written after the fact, or maintained apart from the code, goes stale
quietly.

## Decision

`resources/docs/` holds the project's specifications, and they are part of the code base:

- `adr/`: architecture decision records, one file per decision, named `YYYYMMDD-title.md`, with `Status`,
  `Date`, `Context`, `Decision` and `Consequences`.
- `api/`: one OpenAPI 3.1 file per resource for the HTTP routes (`{resource}.openapi.yaml`; the starter kit
  ships `base-app`, `base-auth` and `base-settings`) and one AsyncAPI file per aggregate for its domain events
  (`{aggregate}.asyncapi.yaml`); what belongs in AsyncAPI is bounded by
  `20261104-asyncapi-domain-events-only.md`.
- `DEPLOYMENT.md`: environments, configuration, CI and production deployment.
- `ER.md`: the entity-relationship diagram of the database tables.
- `agents/`: the rules for coding agents, indexed from `AGENTS.md`.

Rules for keeping them true:

1. A change to routes, validation, responses, events, tables, configuration or infrastructure updates the
   matching specification in the same commit as the code.
2. Specifications are verified against the code (route list, request `rules()`, migrations, config files),
   never written from memory and never describing planned behavior.
3. Each specification is self-contained; OpenAPI and AsyncAPI files do not reference other files.
4. A decision that changes direction gets a new ADR; an accepted ADR is not rewritten to hide history. It
   is superseded or refined by a later one that says so.

## Consequences

- Reviewers can check a code change and its specification in one diff, and a change without its
  specification is incomplete.
- Agents and people read the same sources; instructions for agents are versioned with the code they
  describe.
- Writing the specifications costs time on every change that touches a contract, and a wrong specification is
  worse than none, hence the verification rule.
- Tests can enforce parts of the contract: for events, `tests/Unit/Docs/AsyncApiScopeTest.php` checks that
  the AsyncAPI files and the domain event classes agree.
- What AsyncAPI covers is refined by `20261104-asyncapi-domain-events-only.md`, which narrows the AsyncAPI
  coverage to domain events without superseding this decision.
