# Laravel and PHP

Framework rules come from the generated
[Laravel Boost guidelines](laravel-boost.md). This file
records project rules and the places where they override Boost.

## Overrides of the Boost guidelines

- Create entities and slice classes by hand. `php artisan make:model` and
  `make:controller` write to `app/`, where domain code does not belong. Use
  `make:migration`, `make:factory {Aggregate}/{Entity}Factory`, and `make:test`.
- No API versioning and no `routes/api.php`; see
  [architecture.md](architecture.md#routes).
- Format with `vendor/bin/pint --dirty --format agent`, not the whole tree.
- Update specs in `resources/docs/` without being asked, as
  [resources/docs/AGENTS.md](../AGENTS.md) requires.
- Domain events follow [architecture.md](architecture.md#domain-events).

## Validation, authorization, and errors

- **Validation:** every slice Request extends
  `App\Support\AuthenticatedRequest` and declares `rules()`. Mark server-owned
  fields (`id`, `uuid`, `user_id`, timestamps) `prohibited`. Expose validated
  data through typed accessors (`getTaskValues()`) built on `validated()` or
  `safe()->only([...])`; never pass `$request->all()` to models.
- **Authentication:** routes apply `auth` (and `verified` where required).
  `AuthenticatedRequest` rejects a missing or non-`User` identity with `401`
  and provides `getAuthenticatedUser(): User`.
- **Authorization:** Handlers enforce ownership with owner-scoped queries
  ending in `firstOrFail()`, so another user's record returns `404` without
  revealing that it exists. Add a policy only when access rules go beyond
  ownership.
- **Transactions:** writing Handlers wrap the whole operation, owner-scoped
  lookup and every related write included, in `DB::transaction()` inside
  `execute()`. Read Handlers do not open transactions.
- **Errors:** let exceptions propagate; do not catch and swallow them. There
  are two exceptions, both reported. A block of a page that loads on its own,
  such as a dashboard block, is read through `rescue()` with reporting on (the
  default), so the failure is logged, and sends null for that block so the page
  shows it as failed (a hypothetical `ShowReportHandler`). A whole page that fails the same
  way uses `App\Support\DataFailure::onRead(fn)`, which also lets expected
  exceptions through, such as a `404`. A write is the other: its
  controller wraps `execute()`, outside the transaction so the rollback has
  already run, in `App\Support\DataFailure::onWrite(fn, message)`. It reports
  an unexpected failure once and rethrows it as a `ValidationException` on the
  key `save`, so the form redirects back with its state and shows the message.
  Validation, HTTP, not-found, authentication and authorization exceptions pass
  through unreported. Never use a bare `rescue()` on a write: it would answer
  as if the write had happened. A reported `rescue()` is not swallowing; a
  silent one (`report: false`) or an empty `catch` still is. Laravel maps
  `ModelNotFoundException` to `404` and `ValidationException` to `422` JSON or
  a redirect with errors for Inertia forms, and an exception inside a
  transaction rolls it back. Reject malformed route parameters with
  `abort_unless(..., 404)`. `bootstrap/app.php` keeps Laravel's default
  exception rendering.
- **User feedback:** controllers that redirect report success with
  `Inertia::flash('toast', ['type' => 'success', 'message' => __('...')])`, as
  the settings controllers do.

## Models and factories

Entities are plural; attributes are singular snake_case except collection
attributes, which are plural. Document every column with `@property` and every
relationship with `@property-read`, using real types: primitives,
`array<string, mixed>` for JSON objects, `Collection<int, T>` for collection
attributes, `CarbonImmutable` for timestamps, the related entity, or
`EloquentCollection<int, Entity>`.

```php
// src/Tasks/Models/Tasks.php
namespace Tasks\Models;

use App\Models\Traits\HasUuid;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\Tasks\TasksFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $subject
 * @property array<string, mixed>|null $metadata
 * @property Collection<int, string> $tags
 * @property CarbonImmutable|null $due_date
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $user
 * @property-read EloquentCollection<int, TaskComments> $comments
 */
#[Fillable(['subject', 'metadata', 'tags', 'due_date'])]
class Tasks extends Model
{
    /** @use HasFactory<TasksFactory> */
    use HasFactory, HasUuid;

    protected static string $factory = TasksFactory::class;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<TaskComments, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComments::class, 'task_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'tags' => AsCollection::class,
            'due_date' => 'datetime',
        ];
    }
}

// database/factories/Tasks/TasksFactory.php
namespace Database\Factories\Tasks;

/**
 * @extends Factory<Tasks>
 */
class TasksFactory extends Factory
{
    protected $model = Tasks::class;
}
```

- **Factory link:** the entity sets `protected static string $factory`, and
  the factory declares `protected $model = Entity::class;`. Laravel only
  guesses models in `App\Models`; without `$model`, `make()` and `create()`
  fail with `Class "App\Tasks" not found`.
- **Tables:** Laravel derives `tasks` and `task_comments` from the plural class
  names, so entities need no `$table`.
- **Foreign keys:** always pass them to relationship methods. Columns are
  singular (`task_id`), but Laravel guesses `tasks_id` from the plural class.
- **Casts:** JSON to `array`, timestamps to `datetime`, enum columns to their
  enum class. `AppServiceProvider` sets `Date::use(CarbonImmutable::class)`, so
  every timestamp is `CarbonImmutable`.
- **Collections:** cast JSON lists such as `tags` with `AsCollection::class`.
  Never use the `'collection'` string cast: it silently drops in-place changes
  such as `$task->tags->push('php')` on save.

## Octane

- Never keep request-specific state in static properties, singletons, or
  container instances that outlive a request. Prefer `$this->app->scoped()`.
- The development server runs FrankenPHP with `--workers=1 --max-requests=1`,
  so leaks do not reproduce in the browser. Cover request-scoped changes in
  `tests/Feature/OctaneRequestIsolationTest.php`.

## Domain skills

Read the matching skill before working in its domain, when it is installed.

| Work                                                             | Skill                     |
| ---------------------------------------------------------------- | ------------------------- |
| Laravel PHP, routes, Eloquent, migrations, backend reviews       | `laravel-best-practices`  |
| Laravel authentication, Fortify, profile changes, 2FA, passkeys  | `fortify-development`     |
| Inertia Vue pages, forms, navigation, client behavior            | `inertia-vue-development` |
| Frontend calls to backend routes or controller actions           | `wayfinder-development`   |
| Test design, coverage, assertions, isolation, test reviews       | `testing-best-practices`  |
| Octane drivers, concurrency, worker state, memory, configuration | `octane-development`      |
| Explicit requests to infer and record project conventions        | `infer-conventions`       |
