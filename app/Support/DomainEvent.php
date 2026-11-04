<?php

namespace App\Support;

/**
 * Marks a domain event: a business-level fact that an aggregate's state
 * changed, named in the past tense (`TaskCompleted`) and defined in
 * `src/{Aggregate}/Events/`. Only classes implementing this interface may be
 * documented in `resources/docs/api/*.asyncapi.yaml`, and every one of them
 * must be (tests/Unit/Docs/AsyncApiScopeTest.php). Framework events
 * (queue, database, mail, authentication, Octane) never implement it.
 */
interface DomainEvent {}
