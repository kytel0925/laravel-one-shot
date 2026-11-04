# Aggregates (`src/`)

Full rules: [architecture](../resources/docs/agents/architecture.md) (slices,
naming, routes, domain events, checklists) and
[Laravel and PHP](../resources/docs/agents/laravel-php.md) (validation, models,
factories).

```text
src/
└── {Aggregate}/                        PSR-4 root "{Aggregate}\\" in composer.json
    ├── Features/
    │   └── {Action}/                   one folder per use case
    │       ├── {Action}Controller.php  invokable; calls the Handler, returns the Resource
    │       ├── {Action}Handler.php     business logic; one public execute()
    │       ├── {Action}Request.php     validation rules and typed accessors
    │       └── {Action}Resource.php    response shape: uuid and public fields
    ├── Models/
    │   ├── {Entity}.php                root and child entities
    │   ├── Enums/
    │   │   └── {Name}.php              backed enums owned by the aggregate
    │   └── Traits/
    │       └── {Name}.php              model traits used only by the aggregate
    ├── Events/
    │   └── {Name}.php                  domain events, past tense (TaskCompleted)
    ├── Listeners/
    │   └── {Name}.php                  reactions to domain events
    ├── Common/
    │   └── {Name}.php                  helpers shared by the aggregate's features
    └── routes.php                      loaded automatically by routes/web.php
```

- Aggregates and entities are plural PascalCase (`Tasks`, `TaskComments`).
  Attributes and columns are singular snake_case (`due_date`, `task_id`),
  except collection attributes, which are plural (`tags`). Register each new
  root in `composer.json` and run `composer dump-autoload`.
- Code shared by several aggregates stays in the `App` namespace
  (`App\Support`, `App\Models\Traits`).
- Child entities and their features (`CreateComment`) stay in the root's
  folders. Never import another aggregate's models or `Common/`; call its
  Handler or dispatch a domain event
  ([domain events](../resources/docs/agents/architecture.md#domain-events)).
- **Request:** extends `App\Support\AuthenticatedRequest`; declares `rules()`,
  marks server-owned fields `prohibited`, and exposes typed accessors over
  `validated()` or `safe()->only([...])`.
- **Handler:** receives its Request by constructor injection; one public
  `execute()` takes route parameters. Lookups are owner-scoped and end in
  `firstOrFail()`. Writes run inside one `DB::transaction()` in `execute()`.
- **Controller:** invokable; receives the Handler by constructor injection and
  returns the Resource (or an Inertia response). No queries or business rules.
- **Resource:** exposes `uuid` and public fields, never `id` or foreign keys.
  Omit it for no-body responses; extend an identical sibling Resource instead
  of duplicating it.
- **Entities:** document every column with `@property` and its type
  (primitives, `array<string, mixed>` for JSON, `Collection<int, T>` for
  collection attributes, `CarbonImmutable` for timestamps) and every
  relationship with `@property-read` and its entity or
  `EloquentCollection<int, Entity>` type. Cast JSON to `array` by default, JSON
  lists such as `tags` to `AsCollection::class` (never the `'collection'`
  string cast, which loses in-place changes), and timestamps to `datetime`.
  Pass foreign keys explicitly to relationships; Laravel would guess plural
  keys (`tasks_id`). Use `App\Models\Traits\HasUuid`.
- **Factories:** the entity sets `protected static string $factory`; the
  factory in `database/factories/{Aggregate}/` declares
  `@extends Factory<Entity>` and `protected $model = Entity::class;`.
- **Routes:** name every route; constrain `{uuid}` with `whereUuid` and
  numeric ids with `whereNumber`.
- Tests mirror this layout under `tests/{Suite}/{Aggregate}/`; see
  [tests/AGENTS.md](../tests/AGENTS.md).
