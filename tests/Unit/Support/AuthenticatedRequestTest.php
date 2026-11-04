<?php

use App\Models\User;
use App\Support\AuthenticatedRequest;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Auth\User as OtherIdentity;

function authenticatedRequestFor(mixed $user): AuthenticatedRequest
{
    $request = new class extends AuthenticatedRequest
    {
        /**
         * @return array<string, mixed>
         */
        public function rules(): array
        {
            return [];
        }
    };

    $request->setUserResolver(fn () => $user);

    return $request;
}

test('it authorizes and returns the signed-in user', function () {
    $user = new User;
    $request = authenticatedRequestFor($user);

    expect($request->authorize())->toBeTrue()
        ->and($request->getAuthenticatedUser())->toBe($user);
});

test('it rejects a guest with an authentication error', function () {
    authenticatedRequestFor(null)->authorize();
})->throws(AuthenticationException::class);

test('it rejects an identity that is not an application user', function () {
    authenticatedRequestFor(new OtherIdentity)->getAuthenticatedUser();
})->throws(AuthenticationException::class);
