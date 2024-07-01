<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2020 Belledonne Communications SARL, All rights reserved.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Awobaz\Compoships\Database\Eloquent\Factories\ComposhipsFactory;

use App\Account;
use App\AccountCreationToken;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;
use App\SipDomain;

class AccountFactory extends Factory
{
    use ComposhipsFactory;
    protected $model = Account::class;

    public function definition()
    {
        $domain = SipDomain::count() == 0
            ? SipDomain::factory()->create()
            : SipDomain::first();

        return [
            'username' => $this->faker->username,
            'display_name' => $this->faker->name,
            'domain' => $domain->domain,
            'user_agent' => $this->faker->userAgent,
            'confirmation_key' => Str::random(WebAuthenticateController::$emailCodeSize),
            'ip_address' => $this->faker->ipv4,
            'created_at' => $this->faker->dateTimeBetween('-1 year'),
            'dtmf_protocol' => array_rand(Account::$dtmfProtocols),
            'activated' => true,
            'admin' => false
        ];
    }

    public function admin()
    {
        return $this->state(fn (array $attributes) => [
            'admin' => true,
        ]);
    }

    public function superAdmin()
    {
        return $this->state(function (array $attributes) {
            $sipDomain = SipDomain::where('domain', $attributes['domain'])->first();
            $sipDomain->super = true;
            $sipDomain->save();

            return [
                'admin' => true,
            ];
        });
    }

    public function deactivated()
    {
        return $this->state(fn (array $attributes) => [
            'activated' => false,
        ]);
    }

    public function withEmail()
    {
        return $this->state(fn (array $attributes) => [
            'email' => $this->faker->email,
        ]);
    }

    public function withConsumedAccountCreationToken()
    {
        return $this->state(fn (array $attributes) => [])->afterCreating(function (Account $account) {
            $accountCreationToken = new AccountCreationToken;
            $accountCreationToken->token = 'test_token';
            $accountCreationToken->account_id = $account->id;
            $accountCreationToken->used = true;
            $accountCreationToken->save();
        });
    }
}
