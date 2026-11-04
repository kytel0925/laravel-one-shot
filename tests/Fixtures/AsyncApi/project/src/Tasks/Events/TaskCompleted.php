<?php

namespace Tasks\Events;

use App\Support\DomainEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final class TaskCompleted implements DomainEvent, ShouldDispatchAfterCommit {}
