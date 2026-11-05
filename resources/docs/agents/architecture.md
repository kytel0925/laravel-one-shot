# Architecture

Domain code is organized as vertical slices grouped by aggregate root. Each
aggregate root is its own PSR-4 namespace in `src/{Aggregate}/`. Code shared by
several aggregates stays in the `App` namespace (`app/Support/`,
`app/Models/Traits/`). The examples use a `Tasks` aggregate with a
`TaskComments` child entity; the starter kit ships with no aggregates.

Model, validation, and error rules are in [laravel-php.md](laravel-php.md).

```text
src/
  Tasks/                          aggregate root, PSR-4 "Tasks\\": "src/Tasks/"
    Features/
      Create/                     one folder per action
        CreateController.php
        CreateHandler.php
        CreateRequest.php
        CreateResource.php
      CreateComment/              child entity feature, still under the root
    Models/
      Tasks.php                   root entity
      TaskComments.php            child entity, under the root
      Enums/TaskStatus.php        backed enums owned by this aggregate
      Traits/HasDueDate.php       model traits used only by this aggregate
    Events/TaskCompleted.php      domain events, past tense
    Listeners/NotifyAssignee.php  reactions to domain events
    Common/                       helpers shared by this aggregate's features
    routes.php                    this aggregate's routes
app/
  Models/
    User.php                      application identity (framework integration)
    Traits/HasUuid.php            model traits shared by several aggregates
  Support/                        cross-aggregate helpers only
```

Register every aggregate root in `autoload.psr-4` in `composer.json`, after
`"App\\"`, and run `composer dump-autoload`:

```json
"Tasks\\": "src/Tasks/",
```

## File roles

| File                 | Responsibility                                                                                                    |
| -------------------- | ----------------------------------------------------------------------------------------------------------------- |
| `{Action}Request`    | Form Request: authorization entry point, validation `rules()`, typed accessors over validated data                |
| `{Action}Handler`    | Business logic. One public `execute()` taking route parameters; owns queries, writes, and transactions            |
| `{Action}Controller` | Invokable and thin: receives the Handler, calls `execute()`, wraps the result in the Resource or Inertia response |
| `{Action}Resource`   | `JsonResource` defining the slice's output; exposes public identifiers only                                       |
| `Models/`            | Eloquent entities: documented attributes and relationships, fillable attributes, casts. No business workflows     |
| `Models/Enums/`      | Backed enums for persisted states, cast on the model                                                              |
| `Models/Traits/`     | Model behavior used only by this aggregate                                                                        |
| `Events/`            | Domain events: past-tense facts that the aggregate's state changed (see [Domain events](#domain-events))          |
| `Listeners/`         | Reactions to domain events, from this or another aggregate                                                        |
| `Common/`            | Helpers shared by this aggregate's features                                                                       |

The Controller receives its Handler by constructor injection and the Handler
receives its Request by constructor injection, so Laravel validates and
authorizes the request before the Handler runs.

A feature that returns no body (for example `204` after a delete) has no
Resource. When a slice returns exactly another slice's representation, its
Resource extends that Resource (`class CreateResource extends ShowResource {}`)
instead of duplicating the mapping.

## Reference slice

```php
// src/Tasks/Features/Create/CreateRequest.php
namespace Tasks\Features\Create;

use App\Support\AuthenticatedRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class CreateRequest extends AuthenticatedRequest
{
    /**
     * @return array<string, mixed>
     */
    public function getTaskValues(): array
    {
        return $this->validated();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date'],
            'uuid' => ['prohibited'],
            'user_id' => ['prohibited'],
        ];
    }
}

// src/Tasks/Features/Create/CreateHandler.php
class CreateHandler
{
    public function __construct(private readonly CreateRequest $request) {}

    public function execute(): Tasks
    {
        return DB::transaction(fn (): Tasks => $this->request->getAuthenticatedUser()
            ->tasks()
            ->create($this->request->getTaskValues()));
    }
}

// src/Tasks/Features/Create/CreateController.php
class CreateController
{
    public function __construct(private readonly CreateHandler $handler) {}

    public function __invoke(): JsonResponse
    {
        return (new CreateResource($this->handler->execute()))->response()->setStatusCode(201);
    }
}

// src/Tasks/Features/Create/CreateResource.php
/**
 * @mixin Tasks
 */
class CreateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'subject' => $this->subject,
            'due_date' => $this->due_date?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
```

A Handler that reads a record by route parameter takes it as an argument and
scopes the lookup to the owner:
`$this->request->getAuthenticatedUser()->tasks()->where('uuid', $uuid)->firstOrFail()`.

## Aggregate boundaries

- A feature belongs to exactly one aggregate root. Child entities and their
  features live in the root's folders: `src/Tasks/Models/TaskComments.php`,
  `src/Tasks/Features/CreateComment/`.
- Reach a child through its root: look up the owned root first, then the child
  through the root's relationship.
- A slice never queries or writes another aggregate's models, and never
  imports another aggregate's `Common/`. Call that aggregate's Handler or
  dispatch a domain event.
- `App\Models\User` is the application identity, not an aggregate. Ownership
  relationships such as `User::tasks()` live on it.
- Put code in `app/Support/` or `app/Models/Traits/` only when more than one
  aggregate uses it. Do not abstract before the duplication exists.
- Exception to "the Handler receives its Request": a Handler that another
  aggregate must call inside its own transaction takes plain arguments instead
  (`ReserveStockHandler::execute(int $productId, int $quantity)`). Its own
  controller still builds them from its Request. Record each such Handler in an
  ADR.
- Framework integration (Fortify actions, settings controllers and requests,
  middleware, providers, `app/Concerns/`) stays in Laravel's default places
  under `app/` and is not sliced.

## Reading data that does not exist yet

A slice that shows data another feature will produce (a dashboard's
indicators, pending requests, an agenda) reads it through a port: an
interface in the aggregate's `Common/` (`Reports\Common\DashboardSource`)
with plain DTOs and enums beside it. A null implementation is the default
binding and returns the empty state; tests bind a fake. Bind with `bind` or
`scoped`, never `singleton`. The Handler, not the source, sorts and limits what
the page shows, so a real source cannot break the contract. The page's route
stays in `routes/web.php`, with its slice under the aggregate that owns the
data (`src/Reports/Features/ShowDashboard/`). The names are hypothetical: the
starter kit's dashboard is still a placeholder,
`Route::inertia('dashboard', 'Dashboard')` in `routes/web.php`.

Access to such a page is decided in the Handler from the member's enum
(`Role::seesPanel()`), never from a display label, and a value a role must not
see is left out of the props (the key is absent, not null) rather than hidden
in Vue. A role without access gets a separate page with no data props, and the
Handler does not read the source for it. A member with no role is treated like
a role without access; that check lives in the Handler because null cannot be
an enum method. Put each rule on the enum so a role added later fails closed.
When a whole feature is closed to a role, gate it in the Request
(`authorize()` fails with `403` before any query) instead of serving a page
without data.

## Domain events

A domain event is a business-level fact that an aggregate's state changed. It
is how one aggregate reacts to another without importing its models.

- **Definition:** `src/{Aggregate}/Events/{Name}.php`, named in the past tense
  (`TaskCompleted`), implementing the marker interface
  `App\Support\DomainEvent`.
- **Dispatch:** the event implements `ShouldDispatchAfterCommit`, so listeners
  run only once the Handler's transaction has committed. The Handler
  dispatches it from `execute()`.
- **Payload:** public readonly properties holding scalars, enums, and `uuid`
  values, never Eloquent models, `id`, or foreign keys.
- **Listeners:** `src/{Aggregate}/Listeners/`, one class per reaction, with a
  typed `handle(TaskCompleted $event)`. `bootstrap/app.php` discovers every
  `src/*/Listeners` folder and maps its files to the aggregate's PSR-4 root
  (`App\Support\AggregateClassName`), so no manual `Event::listen()` is
  needed; confirm with `php artisan event:list`. A listener that implements
  `ShouldQueue` runs on the RabbitMQ queue.
- **Scope:** only domain events are documented in AsyncAPI. Framework and
  infrastructure events are never documented, even when the application
  listens to them. The full policy is in
  [AsyncAPI scope](../AGENTS.md#asyncapi-scope).

```php
// src/Tasks/Events/TaskCompleted.php
namespace Tasks\Events;

use App\Support\DomainEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class TaskCompleted implements DomainEvent, ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly string $taskUuid) {}
}
```

## Naming

| Element               | Convention                                                | Example                                           |
| --------------------- | --------------------------------------------------------- | ------------------------------------------------- |
| Aggregate root        | Plural PascalCase folder and PSR-4 root namespace         | `src/Tasks/`, `Tasks\`                            |
| Entities              | Plural PascalCase, `{Aggregate}\Models\{Entity}`          | `Tasks\Models\Tasks`, `Tasks\Models\TaskComments` |
| Attributes, columns   | Singular snake_case; foreign keys `{entity}_id`           | `subject`, `due_date`, `task_id`                  |
| Collection attributes | Plural snake_case JSON list cast with `AsCollection`      | `tags`                                            |
| Tables                | Plural snake_case, derived from the entity name           | `tasks`, `task_comments`                          |
| Action folder         | PascalCase verb, plus child name for children             | `Create/`, `Complete/`, `CreateComment/`          |
| Slice classes         | `{Aggregate}\Features\{Action}\{Action}{Role}`            | `Tasks\Features\Create\CreateHandler`             |
| Enums and traits      | `{Aggregate}\Models\Enums\{Name}`, `Models\Traits\{Name}` | `Tasks\Models\Enums\TaskStatus`                   |
| Common helpers        | `{Aggregate}\Common\{Name}`                               | `Tasks\Common\ResolvesCommentId`                  |
| Domain events         | `{Aggregate}\Events\{PastTenseName}`                      | `Tasks\Events\TaskCompleted`                      |
| Listeners             | `{Aggregate}\Listeners\{Reaction}`                        | `Tasks\Listeners\NotifyAssignee`                  |
| Factories             | `database/factories/{Aggregate}/{Entity}Factory.php`      | `Database\Factories\Tasks\TasksFactory`           |
| Feature tests         | `tests/Feature/{Aggregate}/Features/{Action}Test.php`     | `tests/Feature/Tasks/Features/CreateTest.php`     |
| Inertia page routes   | `{aggregate}.{page}`, lowercase                           | `tasks.index`                                     |
| JSON endpoint routes  | `api.{aggregate}.{action}` under `/api/{aggregate}`       | `api.tasks.store`, `api.tasks.comments.update`    |

## UUIDs

- Domain tables keep an auto-incrementing `id` for foreign keys and add a
  unique `uuid` column (`$table->uuid()->unique()`) as the public identifier.
- Entities use `App\Models\Traits\HasUuid`, which generates `uuid` and binds
  routes by it.
- Routes take `{uuid}` with `->whereUuid('uuid')`. Resources expose `uuid`,
  never `id` or foreign keys. Child entities without a `uuid` column use their
  numeric `id` with `->whereNumber(...)`.

## Routes

Each aggregate defines `src/{Aggregate}/routes.php`. `routes/web.php` requires
every such file, so it runs inside the `web` middleware group (session, CSRF,
Inertia). Each file sets its own prefix, name prefix, and auth middleware, and
names every route:

```php
Route::middleware('auth')->prefix('api/tasks')->name('api.tasks.')->group(function () {
    Route::post('/', CreateController::class)->name('store');
    Route::put('/{uuid}/complete', CompleteController::class)->whereUuid('uuid')->name('complete');
});
```

Pages that require a verified account use `['auth', 'verified']`. Home (the
`Welcome` page), dashboard, and settings routes stay in `routes/web.php` and
`routes/settings.php`; Fortify registers the authentication routes itself.
There is no `routes/api.php` and no API versioning: JSON endpoints use the
browser session and CSRF protection, not tokens.

## Checklists

`/new-aggregate` and `/new-feature` in Claude Code point to these steps.

### Add an aggregate

1. Choose the plural PascalCase root name (`Tasks`) and confirm it is a root,
   not a child of an existing aggregate.
2. Register it in `composer.json` and run `composer dump-autoload`.
3. `php artisan make:migration create_{table}_table`: incrementing `id`,
   unique `uuid`, singular snake_case columns, `{entity}_id` foreign keys.
4. Create the entity by hand in `src/{Aggregate}/Models/` and its factory with
   `php artisan make:factory {Aggregate}/{Entity}Factory`, following
   [laravel-php.md](laravel-php.md#models-and-factories). Add the ownership
   relationship to `User` when users own it.
5. Create `src/{Aggregate}/routes.php` with the prefix, name prefix, and auth
   middleware.
6. Create `Models/Enums/`, `Models/Traits/`, and `Common/` when needed.
7. Add `resources/docs/api/{aggregate}.openapi.yaml`, update `ER.md`, and
   write an ADR (see [resources/docs/AGENTS.md](../AGENTS.md)). When the
   aggregate dispatches or listens to domain events, also add
   `api/{aggregate}.asyncapi.yaml`.

### Add a feature

1. Identify the owning aggregate; child entity features go under the root.
2. Create `src/{Aggregate}/Features/{Action}/` with the four slice classes,
   following the reference slice.
3. Register a named route with `whereUuid` / `whereNumber` constraints.
4. Write `tests/Feature/{Aggregate}/Features/{Action}Test.php` (see
   [testing.md](testing.md)).
5. When the frontend calls it, run `npm run build` and use the generated
   Wayfinder helper.
6. Update the aggregate's OpenAPI spec, and its AsyncAPI spec when the feature
   dispatches or listens to a domain event (see
   [Domain events](#domain-events)).
