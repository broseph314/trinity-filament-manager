<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\Trinity\AccountProvisioner;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => $input['password'],
        ])->save();

        $username = optional($user->trinityLink)->trinity_username
            ?? $this->trinityUsernameFromEmail($user->email);

        try {
            app(AccountProvisioner::class)->provisionWithPassword(
                $user,
                username: $username,
                plainPassword: $input['password']
            );
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'password' => 'Password updated locally, but failed to sync to game account. Please try again.',
            ]);
        }

    }
}
