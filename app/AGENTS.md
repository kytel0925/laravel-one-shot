# Application (`app/`)

Domain code does not live here; aggregates live in `src/` (see
[src/AGENTS.md](../src/AGENTS.md)). Full rules:
[architecture](../resources/docs/agents/architecture.md#aggregate-boundaries).

| Path                                                    | Holds                                                                         |
| ------------------------------------------------------- | ----------------------------------------------------------------------------- |
| `Support/`                                              | Cross-aggregate helpers: `AuthenticatedRequest`, `DataFailure`, `DomainEvent` |
| `Models/Traits/`                                        | Model traits shared by several aggregates (`HasUuid`)                         |
| `Models/User.php`                                       | Application identity used by Fortify                                          |
| `Actions/Fortify/`, `Concerns/`                         | Authentication actions and shared validation rules                            |
| `Http/Controllers/Settings/`, `Http/Requests/Settings/` | Profile and security settings                                                 |
| `Http/Middleware/`, `Providers/`                        | Inertia shared props, appearance cookie, configuration                        |

- Never add domain controllers, requests, or models here. Settings code
  follows Laravel's defaults and is not sliced.
- Put code in `Support/` or `Models/Traits/` only when more than one aggregate
  uses it; otherwise it belongs in that aggregate's `Common/` or
  `Models/Traits/`.
- `User` may hold ownership relationships to aggregate models
  (`User::tasks()`).
- Never store request data in static properties or singletons; Octane reuses
  the application between requests.
