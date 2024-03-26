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
use App\AuthToken;
use App\Password;
use Tests\TestCase;

class AccountProvisioningTest extends TestCase
{
    protected $route = '/provisioning';
    protected $accountRoute = '/provisioning/me';
    protected $method = 'GET';

    protected $pnProvider = 'provider';
    protected $pnParam = 'param';
    protected $pnPrid = 'id';

    public function testBaseProvisioning()
    {
        $this->get($this->route)->assertStatus(400);

        $this->withHeaders([
            'x-linphone-provisioning' => true,
        ])->get($this->route)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertDontSee('ha1');
    }

    public function testDisabledProvisioningHeader()
    {
        config()->set('app.provisioning_use_x_linphone_provisioning_header', false);

        $this->get($this->route)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertDontSee('ha1');
    }

    public function testXLinphoneProvisioningHeader()
    {
        $this->withHeaders([
            'x-linphone-provisioning' => true,
        ])->get($this->accountRoute)->assertStatus(302);
    }

    public function testAuthenticatedProvisioning()
    {
        $password = Password::factory()->create();
        $password->account->generateApiKey();

        $this->keyAuthenticated($password->account)
            ->get($this->accountRoute)
            ->assertStatus(400);

        // Ensure that we get the authentication password once
        $this->keyAuthenticated($password->account)
            ->withHeaders([
                'x-linphone-provisioning' => true,
            ])
            ->get($this->accountRoute)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1')
            ->assertSee('contacts-vcard-list');

        // And then twice
        $this->keyAuthenticated($password->account)
            ->withHeaders([
                'x-linphone-provisioning' => true,
            ])
            ->get($this->accountRoute)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1');
    }

    public function testAuthenticatedReProvisioning()
    {
        $password = Password::factory()->create();
        $password->account->display_name = "Anna O'Reily";
        $password->account->save();
        $password->account->generateApiKey();

        $provisioningToken = $password->account->provisioning_token;

        // Regenerate a new provisioning token from the authenticated account
        $this->keyAuthenticated($password->account)
            ->withHeaders([
                'x-linphone-provisioning' => true,
            ])
            ->get('/api/accounts/me/provision')
            ->assertStatus(200)
            ->assertSee('provisioning_token')
            ->assertDontSee($provisioningToken);

        $password->account->refresh();

        // And use the fresh provisioning token
        $this->withHeaders([
                'x-linphone-provisioning' => true,
            ])
            ->get($this->route . '/' . $password->account->provisioning_token)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee($password->account->username)
            ->assertSee($password->account->display_name, false)
            ->assertSee('ha1');
    }

    public function testPasswordResetProvisioning()
    {
        $password = Password::factory()->create();
        $password->account->generateApiKey();

        $currentPassword = $password->password;

        $provioningUrl = route(
            'provisioning.provision',
            [
                'provisioning_token' => $password->account->provisioning_token,
                'reset_password' => true
            ]
        );

        // Check the QRCode
        $this->withHeaders([
                'x-linphone-provisioning' => true,
            ])->get($this->route . '/qrcode/' . $password->account->provisioning_token . '?reset_password')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Qrcode-URL', $provioningUrl);

        // And use the fresh provisioning token
        $this->withHeaders([
                'x-linphone-provisioning' => true,
            ])->get($provioningUrl)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee($password->account->username)
            ->assertSee($password->account->display_name, false)
            ->assertSee('ha1')
            ->assertSee($password->account->passwords()->first()->password);

        $this->assertNotEquals($password->account->passwords()->first()->password, $currentPassword);
    }

    public function testConfirmationKeyProvisioning()
    {
        $response = $this->get($this->route . '/1234');
        $response->assertStatus(404);

        $password = Password::factory()->create();
        $password->account->generateApiKey();
        $password->account->activated = false;
        $password->account->save();

        // Ensure that we get the authentication password once
        $response = $this->withHeaders([
                'x-linphone-provisioning' => true,
            ])
            ->get($this->route . '/' . $password->account->provisioning_token)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1');

        // Check if the account has been activated
        $this->assertEquals(true, Account::where('id', $password->account->id)->first()->activated);

        // And then twice
        $response = $this->get($this->route . '/' . $password->account->provisioning_token)
            ->assertStatus(404);

        $password->account->refresh();

        $provisioningToken = $password->account->provisioning_token;

        // Refresh the provisioning_token
        $admin = Account::factory()->admin()->create();
        $admin->generateApiKey();

        $this->keyAuthenticated($admin)
            ->withHeaders([
                'x-linphone-provisioning' => true,
            ])
            ->json($this->method, '/api/accounts/' . $password->account->id . '/provision')
            ->assertStatus(200)
            ->assertSee('provisioning_token')
            ->assertDontSee($provisioningToken);

        $password->account->refresh();

        $this->assertNotEquals($provisioningToken, $password->account->provisioning_token);

        // And then provision one last time
        $this->withHeaders([
                'x-linphone-provisioning' => true,
            ])
            ->get($this->route . '/' . $password->account->provisioning_token)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1');
    }

    public function testAuthTokenProvisioning()
    {
        // Generate a public auth_token and attach it
        $response = $this->withHeaders([
                'x-linphone-provisioning' => true,
            ])
            ->json('POST', '/api/accounts/auth_token')
            ->assertStatus(201)
            ->assertJson([
                'token' => true
            ]);

        $authToken = $response->json('token');

        $password = Password::factory()->create();
        $password->account->generateApiKey();

        $this->keyAuthenticated($password->account)
            ->withHeaders([
                'x-linphone-provisioning' => true,
            ])
            ->json($this->method, '/api/accounts/auth_token/' . $authToken . '/attach')
            ->assertStatus(200);

        // Use the auth_token to provision the account
        $this->assertEquals(AuthToken::count(), 1);

        $this->withHeaders([
                'x-linphone-provisioning' => true,
            ])
            ->get($this->route . '/auth_token/' . $authToken)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1');

        $this->assertEquals(AuthToken::count(), 0);

        // Try to re-use the auth_token
        $this->withHeaders([
                'x-linphone-provisioning' => true,
            ])
            ->get($this->route . '/auth_token/' . $authToken)
            ->assertStatus(404);
    }
}
