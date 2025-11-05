<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\Trinity\AccountProvisioner;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {

        $user = null;
        try {
            Validator::make($input, [
                'name' => [
                    'required',
                    'string',
                    'max:32',
                    'regex:/^[A-Za-z0-9]+$/',  // only letters & numbers
                    'unique:users,name',        // optional: if usernames must be unique
                ],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique(User::class),
                ],
                'password' => $this->passwordRules(),
            ])->validate();

            $user = User::create([
                'name' => $input['name'],
                'email' => strtolower($input['email']),
                'password' => Hash::make($input['password']),
            ]);

            // Immediately mirror to Trinity — do NOT queue (avoid serializing plaintext)
            app(AccountProvisioner::class)->provisionWithPassword(
                $user,
                username: $user->name,
                plainPassword: $input['password'],
            );

        } catch(\Throwable $e) {
            // Rollback user creation on any failure
            if($user)
            {
                $user->delete();
            }
            throw $e;
        }

        return $user;
    }
}
