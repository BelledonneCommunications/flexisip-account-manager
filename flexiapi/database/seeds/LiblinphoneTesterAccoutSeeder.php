<?php

namespace Database\Seeders;

use App\Account;
use App\SipDomain;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * This seeder is only used for liblinphone related tests
 * The JSON MUST respect the following format. type: range requires a range object.
 * passwords is optionnal.
 * [
 *   {"type": "account", "id": <id>, "username": "<username>", "domain": "<domain>",
 *       "passwords": [{ "hash": "<hash>", "algorithm": "<algorithm>"}]
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
        $domains = [];

        foreach ($json as $element) {
            if ($element->type == 'account') {
                array_push(
                    $accounts,
                    $this->generateAccountArray(
                        $element->id,
                        $element->username,
                        $element->domain,
                        $element->phone ?? null,
                        $element->activated ?? true,
                        $element->confirmation_key ?? null
                    )
                );

                if(!in_array($element->domain, $domains)) array_push($domains, $element->domain);

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
            }

            if ($element->type == 'range') {
                for ($i = 0; $i < $element->iteration; $i++) {
                    array_push(
                        $accounts,
                        $this->generateAccountArray(
                            str_replace('%id%', $element->idStart + $i, $element->id),
                            str_replace('%usernamePostfix%', $element->usernameStart + $i, $element->username),
                            $element->domain,
                            $element->phone ?? null,
                            $element->activated ?? true,
                            $element->confirmation_key ?? null
                        )
                    );

                    array_push(
                        $passwords,
                        $this->generatePasswordArray($element->idStart + $i, 'secret', 'CLRTXT')
                    );
                }

                if(!in_array($element->domain, $domains)) array_push($domains, $element->domain);
            }
        }

        // Ensure that we clear previous ones
        $ids = array_map(function($account) { return (int)$account['id']; }, $accounts);

        Account::withoutGlobalScopes()->whereIn('id', $ids)->delete();

        // Create the domains
        foreach ($domains as $domain) {
            $sipDomain = SipDomain::where('domain', $domain)->firstOrNew();
            $sipDomain->domain = $domain;
            $sipDomain->save();
        }

        // And seed the fresh ones
        DB::table('accounts')->insert($accounts);
        DB::table('passwords')->insert($passwords);
    }

    private function generateAccountArray(
        int $id, string $username, string $domain, string $phone = null,
        bool $activated = true, string $confirmationKey = null
    ): array {
        return [
            'id' => $id,
            'username' => $username,
            'domain' => $domain,
            'phone' => $phone,
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
}
