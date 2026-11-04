<?php

use Tests\Fixtures\AsyncApi\TaskCompleted;
use Tests\Support\AsyncApiScope;

/*
 * AsyncAPI documents domain events only (resources/docs/AGENTS.md,
 * "AsyncAPI scope"): never framework or package events.
 */

$specs = glob(dirname(__DIR__, 3).'/resources/docs/api/*.asyncapi.yaml') ?: [];

test('every AsyncAPI channel is a domain event', function () use ($specs) {
    $violations = array_merge([], ...array_map(AsyncApiScope::violations(...), $specs));

    expect($violations)->toBe([]);
});

test('every domain event under src/ is documented in AsyncAPI', function () use ($specs) {
    $documented = array_merge([], ...array_map(AsyncApiScope::channelAddresses(...), $specs));

    $undocumented = array_values(array_diff(AsyncApiScope::domainEventsIn(dirname(__DIR__, 3)), $documented));

    expect($undocumented)->toBe([]);
});

test('the scope check accepts domain events', function () {
    $spec = dirname(__DIR__, 2).'/Fixtures/AsyncApi/domain.asyncapi.yaml';

    expect(AsyncApiScope::channelAddresses($spec))->toBe([TaskCompleted::class])
        ->and(AsyncApiScope::violations($spec))->toBe([]);
});

test('the scope check rejects framework events and non-events', function () {
    $violations = AsyncApiScope::violations(dirname(__DIR__, 2).'/Fixtures/AsyncApi/framework.asyncapi.yaml');

    expect($violations)->toHaveCount(4)
        ->and($violations[0])->toContain('Illuminate\Auth\Events\Registered is a framework or package event')
        ->and($violations[1])->toContain('Illuminate\Queue\Events\JobProcessed is a framework or package event')
        ->and($violations[2])->toContain('Laravel\Fortify\Events\TwoFactorAuthenticationEnabled is a framework or package event')
        ->and($violations[3])->toContain('App\Models\User does not implement App\Support\DomainEvent');
});
