<?php

namespace App\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Turns an unexpected data failure into something the page can show,
 * after reporting it once. Expected exceptions (validation, HTTP, not
 * found, authentication, authorization) pass through unreported.
 */
final class DataFailure
{
    /**
     * Runs a whole-page read. An unexpected failure is reported and the
     * page gets null, so it can render its failed state.
     *
     * @template TValue
     *
     * @param  callable(): TValue  $read
     * @return TValue|null
     */
    public static function onRead(callable $read): mixed
    {
        try {
            return $read();
        } catch (Throwable $exception) {
            if (self::isExpected($exception)) {
                throw $exception;
            }

            report($exception);

            return null;
        }
    }

    /**
     * Runs a write; call it outside the Handler's transaction so the
     * rollback has already happened. An unexpected failure is reported and
     * rethrown as a validation error on the `save` key, so the form
     * redirects back with its state and shows the message.
     *
     * @template TValue
     *
     * @param  callable(): TValue  $write
     * @return TValue
     *
     * @throws ValidationException
     */
    public static function onWrite(callable $write, string $message): mixed
    {
        try {
            return $write();
        } catch (Throwable $exception) {
            if (self::isExpected($exception)) {
                throw $exception;
            }

            report($exception);

            throw ValidationException::withMessages(['save' => $message]);
        }
    }

    private static function isExpected(Throwable $exception): bool
    {
        return $exception instanceof ValidationException
            || $exception instanceof HttpExceptionInterface
            || $exception instanceof ModelNotFoundException
            || $exception instanceof AuthenticationException
            || $exception instanceof AuthorizationException;
    }
}
