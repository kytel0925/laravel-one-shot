<?php

use Illuminate\Cache\RedisStore;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Session\CacheBasedSessionHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

/*
 * Proves that the suite runs against the Docker Compose services
 * (phpunit.xml) and not SQLite or in-memory array drivers.
 */

test('the database is the testing PostgreSQL database', function () {
    expect(DB::connection()->getDriverName())->toBe('pgsql')
        ->and(DB::connection()->getDatabaseName())->toBe('testing')
        ->and(DB::scalar('select version()'))->toStartWith('PostgreSQL');
});

test('the cache store is Redis', function () {
    $key = 'infrastructure-'.Str::random();

    Cache::put($key, 'ok', 10);

    expect(config('cache.default'))->toBe('redis')
        ->and(Cache::getStore())->toBeInstanceOf(RedisStore::class)
        ->and(Cache::get($key))->toBe('ok');

    Cache::forget($key);
});

test('sessions are stored in Redis', function () {
    expect(config('session.driver'))->toBe('redis')
        ->and(session()->getHandler())->toBeInstanceOf(CacheBasedSessionHandler::class);
});

test('the queue connection is RabbitMQ and round-trips a message', function () {
    $queue = Queue::connection();
    $name = 'infrastructure-'.Str::lower(Str::random(8));

    expect($queue)->toBeInstanceOf(RabbitMQQueue::class);

    try {
        $queue->pushRaw(json_encode(['job' => 'probe', 'data' => ['ok' => true]]), $name);

        $job = retry(10, function () use ($queue, $name): Job {
            return $queue->pop($name) ?? throw new RuntimeException('Message not delivered yet.');
        }, 100);

        expect(json_decode($job->getRawBody(), true)['data'])->toBe(['ok' => true]);

        $job->delete();
    } finally {
        $queue->deleteQueue($name);
    }
});

test('mail is delivered to Mailpit over SMTP', function () {
    $subject = 'Infrastructure probe '.Str::random(12);

    Mail::raw('Mailpit receives this message.', function ($message) use ($subject) {
        $message->to('probe@example.com')->subject($subject);
    });

    $api = 'http://'.config('mail.mailers.smtp.host').':'.env('MAILPIT_API_PORT', 8025).'/api/v1/search';

    $found = retry(10, function () use ($api, $subject): int {
        $total = Http::get($api, ['query' => "subject:\"{$subject}\""])->throw()->json('messages_count');

        return $total > 0 ? $total : throw new RuntimeException('Message not in Mailpit yet.');
    }, 200);

    expect(config('mail.default'))->toBe('smtp')->and($found)->toBe(1);
});
