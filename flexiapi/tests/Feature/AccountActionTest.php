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
use App\AccountAction;
use App\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountActionTest extends TestCase
{
    use RefreshDatabase;

    protected $route = '/api/accounts';
    protected $method = 'POST';

    public function testCreate()
    {
        $password = Password::factory()->create();

        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route.'/'.$password->account->id.'/actions', [
                'key' => '123',
                'code' => '123',
                'protocol' => 'sipinfo'
            ])
            ->assertStatus(201);

        $this->assertEquals(1, AccountAction::count());

        // Missing key
        $this->keyAuthenticated($admin->account)
        ->json($this->method, $this->route.'/'.$password->account->id.'/actions', [
            'code' => '123',
            'protocol' => 'sipinfo'
        ])
        ->assertStatus(422);

        // Invalid protocol
        $this->keyAuthenticated($admin->account)
        ->json($this->method, $this->route.'/'.$password->account->id.'/actions', [
            'key' => 'abc1234',
            'code' => '123',
            'protocol' => 'wrong'
        ])
        ->assertStatus(422);

        // Invalid key
        $this->keyAuthenticated($admin->account)
        ->json($this->method, $this->route.'/'.$password->account->id.'/actions', [
            'key' => 'Abc1234',
            'code' => '123',
            'protocol' => 'wrong'
        ])
        ->assertStatus(422);

        $this->keyAuthenticated($admin->account)
            ->get($this->route.'/'.$password->account->id.'/actions')
            ->assertJson([
                [
                    'key' => '123',
                    'code' => '123',
                    'protocol' => 'sipinfo'
                ]
            ]);
    }

    public function testDelete()
    {
        $password = Password::factory()->create();

        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route.'/'.$password->account->id.'/actions', [
                'key' => '123',
                'code' => '123',
                'protocol' => 'sipinfo'
            ])
            ->assertStatus(201);

        $this->assertEquals(1, AccountAction::count());
        $accountAction = AccountAction::first();

        $this->keyAuthenticated($admin->account)
            ->delete($this->route.'/'.$password->account->id.'/actions/'.$accountAction->id)
            ->assertStatus(200);

        $this->assertEquals(0, AccountAction::count());
    }

    public function testUpdate()
    {
        $password = Password::factory()->create();

        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route.'/'.$password->account->id.'/actions', [
                'key' => '123',
                'code' => '123',
                'protocol' => 'sipinfo'
            ])
            ->assertStatus(201);

        $this->assertEquals(1, AccountAction::count());
        $accountAction = AccountAction::first();

        $this->keyAuthenticated($admin->account)
            ->json('PUT', $this->route.'/'.$password->account->id.'/actions/'.$accountAction->id, [
                'key' => '123',
                'code' => 'abc',
                'protocol' => 'sipinfo'
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($admin->account)
            ->get($this->route.'/'.$password->account->id.'/actions')
            ->assertJson([
                [
                    'code' => 'abc',
                ]
            ]);
    }
}
