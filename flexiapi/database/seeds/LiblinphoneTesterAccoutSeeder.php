<?php

namespace Database\Seeders;

use App\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * This seeder is only used for liblinphone related tests
 * The JSON MUST respect the following format. type: range requires a range object.
 * alias and passwords are optionnal.
 * [
 *   {"type": "account", "id": <id>, "username": "<username>", "domain": "<domain>",
 *       "passwords": [{ "hash": "<hash>", "algorithm": "<algorithm>"}],
 *       "alias": { "alias": "<alias>", "domain": "<domain>"}
 *   },
 *   {"type": "range", "id": "%id%", "username": "user_%usernamePostfix%", "domain": "<domain>",
 *       "iteration": 2000,
 *       "idStart": 49,
 *       "usernameStart": 1,
 *       "passwords": [{ "hash": "<hash>", "algorithm": "<algorithm>"}]
 *   }
 * ]
 */
class LiblinphoneTesterAccoutSeeder extends Seeder
{
    public function run($json)
    {
        $accounts = [];
        $passwords = [];
        $aliases = [];

        foreach ($json as $element) {
            if ($element->type == 'account') {
                array_push(
                    $accounts,
                    $this->generateAccountArray(
                        $element->id,
                        $element->username,
                        $element->domain,
                        $element->activated ?? true,
                        $element->confirmation_key ?? null
                    )
                );

                if (isset($element->passwords)) {
                    foreach ($element->passwords as $password) {
                        array_push(
                            $passwords,
                            $this->generatePasswordArray(
                                $element->id,
                                $password->hash,
                                $password->algorithm
                            )
                        );
                    }
                }

                if (isset($element->alias)) {
                    array_push(
                        $aliases,
                        $this->generateAliasArray(
                            $element->id,
                            $element->alias->alias,
                            $element->alias->domain
                        )
                    );
                }
            }

            if ($element->type == 'range') {
                for ($i = 0; $i < $element->iteration; $i++) {
                    array_push(
                        $accounts,
                        $this->generateAccountArray(
                            str_replace('%id%', $element->idStart + $i, $element->id),
                            str_replace('%usernamePostfix%', $element->usernameStart + $i, $element->username),
                            $element->domain,
                            $element->activated ?? true,
                            $element->confirmation_key ?? null
                        )
                    );

                    array_push(
                        $passwords,
                        $this->generatePasswordArray($element->idStart + $i, 'secret', 'CLRTXT')
                    );
                }
            }
        }

        // Ensure that we clear previous ones
        $ids = array_map(function($account) { return (int)$account['id']; }, $accounts);

        Account::withoutGlobalScopes()->whereIn('id', $ids)->delete();

        // And seed the fresh ones
        DB::table('accounts')->insert($accounts);
        DB::table('passwords')->insert($passwords);
        DB::table('aliases')->insert($aliases);
    }

    private function generateAccountArray(
        int $id, string $username,
        string $domain, bool $activated = true,
        string $confirmationKey = null
    ): array {
        return [
            'id' => $id,
            'username' => $username,
            'domain' => $domain,
            'email' => rawurlencode($username) . '@' . $domain,
            'activated' => $activated,
            'ip_address' => '',
            'confirmation_key' => $confirmationKey,
            'user_agent' => 'FlexiAPI Seeder',
            'created_at' => '2010-01-03 04:30:43'
        ];
    }

    private function generatePasswordArray(
        int $accountId,
        string $password,
        string $algorythm
    ): array {
        return [
            'account_id' => $accountId,
            'password' => $password,
            'algorithm' => $algorythm
        ];
    }

    private function generateAliasArray(
        int $accountId,
        string $alias,
        string $domain
    ): array {
        return [
            'account_id' => $accountId,
            'alias' => $alias,
            'domain' => $domain
        ];
    }
}
