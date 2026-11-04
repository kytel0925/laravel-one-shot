<?php

namespace Tests\Fixtures\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

/**
 * A queued job used only to prove that jobs travel through RabbitMQ.
 */
final class RecordProcessedJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $marker) {}

    public function handle(): void
    {
        Cache::put('processed-'.$this->marker, true, 60);
    }
}
