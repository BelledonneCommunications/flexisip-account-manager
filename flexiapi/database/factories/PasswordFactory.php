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

use App\Account;
use App\Password;
use Illuminate\Database\Eloquent\Factories\Factory;

class PasswordFactory extends Factory
{
    protected $model = Password::class;

    public function definition()
    {
        $account = Account::factory()->create();

        return [
            'account_id' => $account->id,
            'password'   => hash('md5', $account->username.':'.$account->resolvedRealm.':testtest'),
            'algorithm'  => 'MD5',
        ];
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            $account = Account::find($attributes['account_id']);
            $account->admin = true;
            $account->save();

            return $attributes;
        });
    }

    public function sha256()
    {
        return $this->state(function (array $attributes) {
            $account = Account::find($attributes['account_id']);

            return [
                'password'   => hash('sha256', $account->username.':'.$account->resolvedRealm.':testtest'),
                'account_id' => $account->id,
                'algorithm'  => 'SHA-256',
            ];
        });
    }

    public function clrtxt()
    {
        return $this->state(fn (array $attributes) => [
            'password'   => 'testtest',
            'algorithm'  => 'CLRTXT',
        ]);
    }
}
