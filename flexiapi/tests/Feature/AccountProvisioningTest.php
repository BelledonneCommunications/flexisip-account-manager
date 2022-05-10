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

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Password;
use App\Admin;
use App\Account as DBAccount;

class AccountProvisioningTest extends TestCase
{
    use RefreshDatabase;

    protected $route = '/provisioning';
    protected $accountRoute = '/provisioning/me';
    protected $method = 'GET';

    protected $pnProvider = 'provider';
    protected $pnParam = 'param';
    protected $pnPrid = 'id';

    public function testBaseProvisioning()
    {
        $response = $this->get($this->route);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertDontSee('ha1');
    }

    public function testAuthenticatedProvisioning()
    {
        $response = $this->get($this->accountRoute);
        $response->assertStatus(302);

        $password = Password::factory()->create();
        $password->account->generateApiKey();

        // Ensure that we get the authentication password once
        $response = $this->keyAuthenticated($password->account)
            ->get($this->accountRoute)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1')
            ->assertSee('contacts-vcard-list');

        // And then twice
        $response = $this->keyAuthenticated($password->account)
            ->get($this->accountRoute)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1');
    }

    public function testConfirmationKeyProvisioning()
    {
        $response = $this->get($this->route.'/1234');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertDontSee('ha1');

        $password = Password::factory()->create();
        $password->account->generateApiKey();
        $password->account->activated = false;
        $password->account->save();

        // Ensure that we get the authentication password once
        $response = $this->get($this->route.'/'.$password->account->confirmation_key)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1');

        // Check if the account has been activated
        $this->assertEquals(true, DBAccount::where('id', $password->account->id)->first()->activated);

        // And then twice
        $response = $this->get($this->route.'/'.$password->account->confirmation_key)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertDontSee('ha1');

        $password->account->refresh();

        $confirmationKey = $password->account->confirmation_key;

        // Refresh the confirmation_key
        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $this->keyAuthenticated($admin->account)
            ->json($this->method, '/api/accounts/'.$password->account->id.'/provision')
            ->assertStatus(200)
            ->assertSee('confirmation_key')
            ->assertDontSee($confirmationKey);

        $password->account->refresh();

        $this->assertNotEquals($confirmationKey, $password->account->confirmation_key);

        // And then provision one last time
        $this->get($this->route.'/'.$password->account->confirmation_key)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1');
    }
}