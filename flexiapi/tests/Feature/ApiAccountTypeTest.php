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

use App\Password;
use App\AccountType;
use App\Admin;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiAccountTypeTest extends TestCase
{
    protected $route = '/api/account_types';
    protected $method = 'POST';

    public function testCreate()
    {
        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route, [
                'key' => 'phone',
            ])
            ->assertStatus(201);

        $this->assertEquals(1, AccountType::count());

        // Same key
        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route, [
                'key' => 'phone',
            ])
            ->assertJsonValidationErrorFor('key')
            ->assertStatus(422);

        // Missing key
        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route, [])
            ->assertStatus(422);

        // Invalid key
        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route, [
                'key' => 'Abc1234',
            ])
            ->assertStatus(422);

        $this->keyAuthenticated($admin->account)
            ->get($this->route)
            ->assertJson([
                [
                    'key' => 'phone'
                ]
            ]);
    }

    public function testDelete()
    {
        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route, [
                'key' => 'phone',
            ])
            ->assertStatus(201);

        $this->assertEquals(1, AccountType::count());
        $accountType = AccountType::first();

        $this->keyAuthenticated($admin->account)
            ->delete($this->route . '/' . $accountType->id)
            ->assertStatus(200);

        $this->assertEquals(0, AccountType::count());
    }

    public function testUpdate()
    {
        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route, [
                'key' => 'phone',
            ])
            ->assertStatus(201);

        $this->assertEquals(1, AccountType::count());
        $accountType = AccountType::first();

        $this->keyAuthenticated($admin->account)
            ->json('PUT', $this->route . '/' . $accountType->id, [
                'key' => 'door',
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($admin->account)
            ->get($this->route)
            ->assertJson([
                [
                    'key' => 'door',
                ]
            ]);
    }

    public function testAccountAddType()
    {
        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route, [
                'key' => 'phone',
            ])
            ->assertStatus(201)
            ->assertJson([
                'id' => 1,
                'key' => 'phone',
            ]);

        $accountType = AccountType::first();
        $password = Password::factory()->create();

        $this->keyAuthenticated($admin->account)
            ->json($this->method, '/api/accounts/' . $password->account->id . '/types/' . $accountType->id)
            ->assertStatus(200);

        $this->keyAuthenticated($admin->account)
            ->json($this->method, '/api/accounts/' . $password->account->id . '/types/' . $accountType->id)
            ->assertStatus(403);

        $this->keyAuthenticated($admin->account)
            ->get('/api/accounts/' . $password->account->id)
            ->assertJson([
                'types' => [
                    [
                        'id' => $accountType->id,
                        'key' => $accountType->key
                    ]
                ]
            ]);

        // Remove
        $this->keyAuthenticated($admin->account)
            ->delete('/api/accounts/' . $password->account->id . '/types/' . $accountType->id)
            ->assertStatus(200);

        $this->assertEquals(0, DB::table('account_account_type')->count());

        // Retry
        $this->keyAuthenticated($admin->account)
            ->delete('/api/accounts/' . $password->account->id . '/types/' . $accountType->id)
            ->assertStatus(403);
        $this->assertEquals(0, DB::table('account_account_type')->count());
    }
}
