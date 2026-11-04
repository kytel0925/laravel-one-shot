# AsyncAPI documents domain events only

Status: accepted
Date: 2026-11-04

## Context

`20261023-documentation-as-specifications.md` plans one AsyncAPI file per aggregate for its events. A rule
that says every event, listener, notification, or broadcast change belongs in the AsyncAPI file, read
literally, would pull framework events into the specs: Fortify's `Registered` and `Verified`, queue events,
mail, cache and session events, Octane worker events. Those are framework and package events. They are
not contracts the team owns, they change with upgrades without our consent, and documenting them would
bury the business events other parts of the system rely on.

## Decision

AsyncAPI documents domain events only.

A domain event is a business-level state change of an aggregate:

- named in the past tense (`OrderPlaced`, not `PlaceOrder` or `OrderEvent`);
- a class in `src/{Aggregate}/Events/` implementing `App\Support\DomainEvent`;
- dispatched after the transaction commits (`Illuminate\Contracts\Events\ShouldDispatchAfterCommit`).

Never documented, even when the application listens to them: events of Illuminate packages (queue, database,
mail, cache, session, migrations, Eloquent model events), Laravel and Fortify authentication events (`Login`,
`Logout`, `Registered`, `Verified`, `PasswordReset`, `Lockout`, two-factor and passkey events), Octane worker
events, and Inertia or other package events.

Notifications, broadcasts and RabbitMQ messages appear only as `operations` of the channel of the domain event
that causes them. They get no channel of their own.

The rule is enforced by `tests/Unit/Docs/AsyncApiScopeTest.php`. AsyncAPI specs are
`resources/docs/api/*.asyncapi.yaml` or `*.asyncapi.yml` and declare `asyncapi: 3.x`. Every address in
`channels` and `components.channels` must be a class that implements `App\Support\DomainEvent` and
`ShouldDispatchAfterCommit` and lives in `src/{Aggregate}/Events/` (any depth below `Events`). Every
`DomainEvent` class under the `src/` PSR-4 roots must be documented and must live under an `Events/`
folder.

The starter kit has no domain events, so it has no AsyncAPI files.

## Consequences

- AsyncAPI files stay small and describe the contracts the team owns and versions.
- When a feature needs to react to a framework event, the listener is written in code and documented
  nowhere in AsyncAPI; if the reaction is a business fact other parts rely on, the listener dispatches a
  domain event for it.
- Adding a domain event requires the class, the AsyncAPI channel and (when it is delivered by a
  notification, broadcast or RabbitMQ message) the operation, in the same commit; the test fails otherwise.
- Authentication flows are documented only as HTTP routes in the OpenAPI files (`base-auth`), which is where
  their contract lies.
- This refines `20261023-documentation-as-specifications.md`; it does not supersede it.
