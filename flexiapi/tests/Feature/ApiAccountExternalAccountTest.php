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

namespace Tests\Feature;

use App\Account;
use Tests\TestCase;

class ApiAccountExternalAccountTest extends TestCase
{
    protected $route = '/api/accounts';
    protected $method = 'POST';

    public function testCreate()
    {
        $account = Account::factory()->create();
        $admin = Account::factory()->admin()->create();
        $admin->generateApiKey();

        $username = 'foo';

        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $account->id . '/external/')
            ->assertStatus(404);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route . '/' . $account->id . '/external/', [
                'username' => $username,
                'domain' => 'bar',
                'password' => 'password',
                'protocol' => 'UDP'
            ])->assertStatus(201);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route . '/' . $account->id . '/external/', [
                'username' => $username,
                'domain' => 'bar',
                'registrar' => 'bar',
                'password' => 'password',
                'protocol' => 'UDP'
            ])->assertJsonValidationErrors(['registrar']);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $account->id . '/external/')
            ->assertStatus(200)
            ->assertJson([
                'username' => $username,
            ]);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/123/external/')
            ->assertStatus(404);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route . '/' . $account->id . '/external/', [
                'username' => $username . '2',
                'domain' => 'bar',
                'protocol' => 'UDP'
            ])->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route . '/' . $account->id . '/external/', [
                'username' => $username . '2',
                'domain' => 'bar',
                'realm' => 'newrealm',
                'protocol' => 'UDP'
            ])->assertJsonValidationErrors(['password']);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $account->id . '/external/')
            ->assertStatus(200)
            ->assertJson([
                'username' => $username . '2',
            ]);

        $this->keyAuthenticated($admin)
            ->delete($this->route . '/' . $account->id . '/external')
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $account->id . '/external/')
            ->assertStatus(404);
    }
}
