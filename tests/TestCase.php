<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Laravel\Fortify\Features;
use RuntimeException;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

abstract class TestCase extends BaseTestCase
{
    private static bool $queuePurged = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureTestingServices();
        $this->flushTestingRedis();
        $this->purgeTestingQueueOnce();
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }

    /**
     * Refuses to run against the development services (phpunit.xml): a
     * misconfiguration must never reset the developer's database.
     */
    private function ensureTestingServices(): void
    {
        $database = config('database.connections.'.config('database.default').'.database');

        if ($database !== 'testing' || (string) config('database.redis.default.database') !== '2') {
            throw new RuntimeException(
                "Tests must use the `testing` database and Redis DB 2, got [{$database}] and Redis DB ["
                .config('database.redis.default.database').']. Is the configuration cached?',
            );
        }
    }

    /**
     * Cache and sessions live in Redis DB 2 and 3 (phpunit.xml); start every
     * test from empty databases, as RefreshDatabase does for PostgreSQL.
     */
    private function flushTestingRedis(): void
    {
        Redis::connection('default')->flushdb();
        Redis::connection('cache')->flushdb();
    }

    /**
     * Drops messages left in the `testing` RabbitMQ queue by an earlier run.
     */
    private function purgeTestingQueueOnce(): void
    {
        if (self::$queuePurged) {
            return;
        }

        $queue = Queue::connection();

        if ($queue instanceof RabbitMQQueue && $queue->isQueueExists('testing')) {
            $queue->purge('testing');
        }

        self::$queuePurged = true;
    }
}
