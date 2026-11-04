<?php

namespace Tests\Fixtures\AsyncApi;

use App\Support\DomainEvent;

/**
 * A domain event outside an Events/ folder that dispatches immediately:
 * the scope check must reject both.
 */
final class MisplacedEvent implements DomainEvent {}
