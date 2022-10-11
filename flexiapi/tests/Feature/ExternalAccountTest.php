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

use App\Admin;
use App\Account;
use App\ExternalAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalAccountTest extends TestCase
{
    use RefreshDatabase;

    protected $route = '/api/accounts';
    protected $provisioningRoute = '/provisioning/me';
    protected $method = 'POST';

    public function testExternalAccountAttachOnCreate()
    {
        $admin = Admin::factory()->create();
        $password = $admin->account->passwords()->first();
        $password->account->generateApiKey();
        $password->account->save();

        config()->set('app.consume_external_account_on_create', true);

        // Seed an ExternalAccount
        $externalAccount = ExternalAccount::factory()->create();
        $externalAccount->save();

        $response = $this->keyAuthenticated($password->account)
        ->json($this->method, $this->route, [
            'username' => 'test',
            'domain' => 'example.com',
            'algorithm' => 'SHA-256',
            'password' => '123456',
            'activated' => true,
        ]);

        $response->assertStatus(200);

        // No ExternalAccount left
        $response = $this->keyAuthenticated($password->account)
        ->json($this->method, $this->route, [
            'username' => 'test2',
            'domain' => 'example.com',
            'algorithm' => 'SHA-256',
            'password' => '123456',
        ]);

        $response->assertStatus(403);

        $createdAccount = Account::where('username', 'test')->first();
        $createdAccount->generateApiKey();
        $createdAccount->save();

        $response = $this->keyAuthenticated($createdAccount)
            ->get($this->provisioningRoute)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee($externalAccount->identifier)
            ->assertSee('ha1')
            ->assertSee('idkey')
            ->assertSee('depends_on');
    }
}
