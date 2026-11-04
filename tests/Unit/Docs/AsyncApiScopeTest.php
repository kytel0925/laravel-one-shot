<?php

use Tests\Fixtures\AsyncApi\Events\TaskCompleted;
use Tests\Support\AsyncApiScope;

/*
 * AsyncAPI documents domain events only (resources/docs/AGENTS.md,
 * "AsyncAPI scope"): never framework or package events.
 */

$basePath = dirname(__DIR__, 3);
$fixtures = dirname(__DIR__, 2).'/Fixtures/AsyncApi';

test('every AsyncAPI spec documents only domain events', function () use ($basePath) {
    $violations = array_merge([], ...array_map(
        AsyncApiScope::violations(...),
        AsyncApiScope::specsIn($basePath),
    ));

    expect($violations)->toBe([]);
});

test('every domain event under src/ is placed correctly and documented', function () use ($basePath) {
    $documented = array_merge([], ...array_map(
        AsyncApiScope::channelAddresses(...),
        AsyncApiScope::specsIn($basePath),
    ));
    $events = AsyncApiScope::domainEventsIn($basePath);

    expect(array_merge([], ...array_map(AsyncApiScope::placementViolations(...), $events)))->toBe([])
        ->and(array_values(array_diff($events, $documented)))->toBe([]);
});

test('the scope check accepts domain events', function () use ($fixtures) {
    $spec = $fixtures.'/domain.asyncapi.yaml';

    expect(AsyncApiScope::channelAddresses($spec))->toBe([TaskCompleted::class])
        ->and(AsyncApiScope::violations($spec))->toBe([]);
});

test('the scope check rejects framework events and non-events', function () use ($fixtures) {
    $violations = AsyncApiScope::violations($fixtures.'/framework.asyncapi.yaml');

    expect($violations)->toHaveCount(4)
        ->and($violations[0])->toContain('Illuminate\Auth\Events\Registered is a framework or package event')
        ->and($violations[1])->toContain('Illuminate\Queue\Events\JobProcessed is a framework or package event')
        ->and($violations[2])->toContain('Laravel\Fortify\Events\TwoFactorAuthenticationEnabled is a framework or package event')
        ->and($violations[3])->toContain('App\Models\User does not implement App\Support\DomainEvent');
});

test('the scope check rejects old versions, component channels and misplaced events', function () use ($fixtures) {
    $violations = AsyncApiScope::violations($fixtures.'/components.asyncapi.yml');

    expect($violations)->toHaveCount(4)
        ->and($violations[0])->toContain('must declare `asyncapi: 3.x`, found [2.6.0]')
        ->and($violations[1])->toContain('Illuminate\Queue\Events\JobProcessed is a framework or package event')
        ->and($violations[2])->toContain('MisplacedEvent must live in an Events/ folder')
        ->and($violations[3])->toContain('MisplacedEvent must implement Illuminate\Contracts\Events\ShouldDispatchAfterCommit');
});

test('domain events are found at any depth of an aggregate, wherever they are placed', function () use ($fixtures) {
    $project = $fixtures.'/project';

    spl_autoload_register($loader = function (string $class) use ($project): void {
        if (str_starts_with($class, 'Tasks\\')) {
            require_once $project.'/src/'.str_replace('\\', '/', $class).'.php';
        }
    });

    try {
        $events = AsyncApiScope::domainEventsIn($project);
        sort($events);

        expect($events)->toBe([
            'Tasks\Events\Comments\CommentAdded',
            'Tasks\Events\TaskCompleted',
            'Tasks\Models\Misplaced',
        ])
            ->and(AsyncApiScope::placementViolations('Tasks\Events\Comments\CommentAdded'))->toBe([])
            ->and(AsyncApiScope::placementViolations('Tasks\Models\Misplaced'))->toHaveCount(2);
    } finally {
        spl_autoload_unregister($loader);
    }
});
