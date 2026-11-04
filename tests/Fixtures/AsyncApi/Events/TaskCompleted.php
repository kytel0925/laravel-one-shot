<?php

namespace Tests\Fixtures\AsyncApi\Events;

use App\Support\DomainEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

/**
 * A domain event used only to test the AsyncAPI scope check.
 */
final class TaskCompleted implements DomainEvent, ShouldDispatchAfterCommit
{
    public function __construct(public readonly string $taskUuid) {}
}
