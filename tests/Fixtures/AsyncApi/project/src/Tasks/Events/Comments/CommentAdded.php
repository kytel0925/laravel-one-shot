<?php

namespace Tasks\Events\Comments;

use App\Support\DomainEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final class CommentAdded implements DomainEvent, ShouldDispatchAfterCommit {}
