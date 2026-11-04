<?php

namespace Tests\Fixtures\AsyncApi;

use App\Support\DomainEvent;

/**
 * A domain event used only to test the AsyncAPI scope check.
 */
final class TaskCompleted implements DomainEvent
{
    public function __construct(public readonly string $taskUuid) {}
}
