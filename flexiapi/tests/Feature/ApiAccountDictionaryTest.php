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
use App\ContactsList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiAccountDictionaryTest extends TestCase
{
    use RefreshDatabase;

    protected $route = '/api/accounts';
    protected $method = 'POST';

    public function testCreate()
    {
        $password = Password::factory()->create();
        $account = $password->account;

        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $key = 'foo';
        $value = 'bar';
        $newValue = 'yop';
        $secondKey = 'waza';

        // First key
        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route . '/' . $account->id . ' /dictionary/' . $key , [
                'value' => $value
            ])->assertStatus(201);

        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/' . $account->id . ' /dictionary')
            ->assertStatus(200)
            ->assertJson([
                $key => $value
            ]);

        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/' . $account->id)
            ->assertStatus(200)
            ->assertJson([
                'dictionary' => [
                    $key => $value
                ]
            ]);

        // Update
        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route . '/' . $account->id . ' /dictionary/' . $key , [
                'value' => $newValue
            ])->assertStatus(200);

        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/' . $account->id . ' /dictionary')
            ->assertStatus(200)
            ->assertJson([
                $key => $newValue
            ]);

        // Second key
        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route . '/' . $account->id . ' /dictionary/' . $secondKey , [
                'value' => $newValue
            ])->assertStatus(201);


        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/' . $account->id . ' /dictionary')
            ->assertStatus(200)
            ->assertJson([
                $key => $newValue,
                $secondKey => $newValue
            ]);

        // Delete
        $this->keyAuthenticated($admin->account)
            ->delete($this->route . '/' . $account->id . ' /dictionary/' . $key)
            ->assertStatus(200);

        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/' . $account->id . ' /dictionary')
            ->assertStatus(200)
            ->assertJson([
                $secondKey => $newValue
            ]);
    }
}