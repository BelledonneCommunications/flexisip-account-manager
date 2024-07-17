<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2021 Belledonne Communications SARL, All rights reserved.

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

class AccountBlockingTest extends TestCase
{
    protected $route = '/api/accounts';
    protected $method = 'POST';

    public function testBlocking()
    {
        $account = Account::factory()->withConsumedAccountCreationToken()->create();
        $account->generateApiKey();

        config()->set('app.blocking_amount_events_authorized_during_period', 2);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route . '/me/phone/request', [
                'phone' => '+33612312312'
            ])->assertStatus(200);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route . '/me/email/request', [
                'email' => 'foo@bar.com'
            ])->assertStatus(403);
    }

    public function testAdminBlocking()
    {
        $account = Account::factory()->create();
        $account->generateApiKey();

        $admin = Account::factory()->admin()->create();
        $admin->generateApiKey();

        $this->keyAuthenticated($account)
            ->get($this->route . '/me')->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route . '/' . $account->id .'/block')
            ->assertStatus(200);

        $this->keyAuthenticated($account)
            ->get($this->route . '/me')->assertStatus(403);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route . '/' . $account->id .'/unblock')
            ->assertStatus(200);

        $this->keyAuthenticated($account)
            ->get($this->route . '/me')->assertStatus(200);
    }
}
