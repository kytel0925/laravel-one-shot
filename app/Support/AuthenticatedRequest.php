<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base Form Request of every slice. Rejects a missing or non-User identity
 * with 401 before validation runs, and gives Handlers a typed user.
 */
abstract class AuthenticatedRequest extends FormRequest
{
    /**
     * @throws AuthenticationException
     */
    public function authorize(): bool
    {
        $this->getAuthenticatedUser();

        return true;
    }

    /**
     * @throws AuthenticationException
     */
    public function getAuthenticatedUser(): User
    {
        $user = $this->user();

        if (! $user instanceof User) {
            throw new AuthenticationException;
        }

        return $user;
    }
}
