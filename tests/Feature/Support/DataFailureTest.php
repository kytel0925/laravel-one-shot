<?php

use App\Support\DataFailure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Validation\ValidationException;

test('a read returns its value', function () {
    expect(DataFailure::onRead(fn () => 'rows'))->toBe('rows');
});

test('an unexpected read failure is reported and returns null', function () {
    Exceptions::fake();

    expect(DataFailure::onRead(fn () => throw new RuntimeException('database down')))->toBeNull();

    Exceptions::assertReported(RuntimeException::class);
});

test('an expected read failure passes through unreported', function () {
    Exceptions::fake();

    expect(fn () => DataFailure::onRead(fn () => throw new ModelNotFoundException))
        ->toThrow(ModelNotFoundException::class);

    Exceptions::assertNothingReported();
});

test('an unexpected write failure is reported once and becomes a save error', function () {
    Exceptions::fake();

    try {
        DataFailure::onWrite(fn () => throw new RuntimeException('deadlock'), 'Could not save.');
        $this->fail('The write should have failed.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toBe(['save' => ['Could not save.']]);
    }

    Exceptions::assertReportedCount(1);
});

test('a validation failure in a write passes through unreported', function () {
    Exceptions::fake();

    expect(fn () => DataFailure::onWrite(
        fn () => throw ValidationException::withMessages(['name' => 'Required.']),
        'Could not save.',
    ))->toThrow(ValidationException::class);

    Exceptions::assertNothingReported();
});
