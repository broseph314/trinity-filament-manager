<?php

namespace App\Services\Trinity;

use App\Models\TrinityLink;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountProvisioner
{
    public function __construct(private CommandService $soap) {}

    /**
     * Create a Trinity auth account for the given Laravel user and link it.
     * Returns the Trinity account id.
     */
    public function provision(User $user): int
    {
        // 1) Build a unique Trinity username (A–Z 0–9 only, max 32; Trinity is case-insensitive)
        $base = $this->usernameFromEmail($user->email);
        $username = $this->uniqueUsername($base);

        // 2) Pick an initial password (temporary). You’ll likely force-reset later.
        $tempPassword = Str::password(16);

        // 3) Create via SOAP
        // Syntax:  account create <name> <password>
        // (Expansion is auto or set separately; email is not part of this command)
        $this->mustOk(
            $this->soap->run(sprintf('account create %s %s', $username, $tempPassword)),
            'Unable to create Trinity account via SOAP'
        );

        // 4) Fetch the new account id from auth DB
        $account = DB::connection('trinity_auth')
            ->table('account')
            ->whereRaw('LOWER(username) = ?', [mb_strtolower($username)])
            ->first(['id','username']);

        if (! $account) {
            throw new \RuntimeException('Trinity account created but not found in auth.account');
        }

        // 5) Set email in auth DB (SOAP lacks this; safe to set directly)
        DB::connection('trinity_auth')
            ->table('account')
            ->where('id', $account->id)
            ->update([
                'email' => $user->email,
                // optional: 'last_ip' => request()?->ip(),
            ]);

        // 6) Persist the linkage locally
        TrinityLink::updateOrCreate(
            ['user_id' => $user->id],
            ['trinity_account_id' => $account->id, 'trinity_username' => $account->username]
        );

        // (Optional) baseline expansion / flags:
        // $this->soap->run(sprintf('account set addon %s 2', $username)); // WotLK = 2

        return (int) $account->id;
    }

    /**
     * SYNCHRONOUS ONLY - this has plaintext passwords lol
     * Create or link a Trinity account using the provided username & plaintext password.
     * - Username can be the user's email (see notes below).
     * - Password is used once to set SRP6 on Trinity side, not stored locally.
     */
    public function provisionWithPassword(User $user, string $username, string $plainPassword): int
    {

        // Create if missing
        $exists = DB::connection('trinity_auth')
            ->table('account')
            ->whereRaw('LOWER(username)=?', [mb_strtolower($username)])
            ->exists();

        if (! $exists) {
            // `account create <name> <pass>`
            $out = $this->soap->run(sprintf('account create %s %s', $username, $plainPassword));
            $this->throwIfSoapError($out, 'Unable to create Trinity account');
        } else {
            // If it already exists, we’ll still force the password below
        }

        // Force password to match Laravel one:
        // `account set password <name> <new> <confirm>`
        $out = $this->soap->run(sprintf('account set password %s %s %s', $username, $plainPassword, $plainPassword));
        $this->throwIfSoapError($out, 'Unable to set Trinity password');

        // Fetch id + set email field
        $account = DB::connection('trinity_auth')
            ->table('account')
            ->whereRaw('LOWER(username)=?', [mb_strtolower($username)])
            ->first(['id','username']);

        if (! $account) {
            throw new \RuntimeException('Trinity account not found after creation.');
        }

        DB::connection('trinity_auth')
            ->table('account')
            ->where('id', $account->id)
            ->update(['email' => $user->email]);

        TrinityLink::updateOrCreate(
            ['user_id' => $user->id],
            ['trinity_account_id' => $account->id, 'trinity_username' => $account->username]
        );

        return (int) $account->id;
    }

    private function throwIfSoapError(string $output, string $prefix): void
    {
        $s = mb_strtolower($output);
        if (str_contains($s, 'error') || str_contains($s, 'invalid') || str_contains($s, 'fail')) {
            throw new \RuntimeException($prefix.': '.trim($output));
        }
    }

    private function usernameFromEmail(string $email): string
    {
        // take local part, strip non-alnum, collapse, trim, cap at 24 chars, add suffix room
        $local = strstr($email, '@', true) ?: $email;
        $clean = preg_replace('/[^a-z0-9]/i', '', $local) ?: 'player';
        return mb_substr($clean, 0, 24);
    }

    private function uniqueUsername(string $base): string
    {
        $candidate = $base;
        $i = 0;

        while ($this->usernameExists($candidate)) {
            $i++;
            $suffix = (string)$i;
            $candidate = mb_substr($base, 0, 32 - mb_strlen($suffix)) . $suffix;
            if ($i > 9999) {
                throw new \RuntimeException('Could not allocate unique Trinity username');
            }
        }

        return $candidate;
    }

    private function usernameExists(string $username): bool
    {
        return DB::connection('trinity_auth')
            ->table('account')
            ->whereRaw('LOWER(username) = ?', [mb_strtolower($username)])
            ->exists();
    }

    private function mustOk(string $soapOutput, string $message): void
    {
        $s = mb_strtolower($soapOutput);
        if (str_contains($s, 'error') || str_contains($s, 'invalid') || str_contains($s, 'fail')) {
            throw new \RuntimeException($message . ': ' . trim($soapOutput));
        }
    }
}
