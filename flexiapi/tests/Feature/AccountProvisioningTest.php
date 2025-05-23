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
use App\ProvisioningToken;
use App\Space;
use Illuminate\Support\Facades\DB;
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
        Space::truncate();
        Space::factory()->local()->create();
        space(reload: true);

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
        Space::truncate();
        Space::factory()->local()->withoutProvisioningHeader()->create();
        space(reload: true);

        $this->get($this->route)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertDontSee('ha1');
    }

    public function testDontProvisionHeaderDisabled()
    {
        Space::truncate();
        Space::factory()->local()->create();
        space(reload: true);

        $account = Account::factory()->deactivated()->create();
        $account->generateUserApiKey();

        $this->assertEquals(false, $account->activated);
        $this->assertFalse($account->currentProvisioningToken->used);

        // /provisioning/me
        $this->keyAuthenticated($account)
            ->get($this->accountRoute)
            ->assertStatus(400);

        $account->refresh();

        $this->assertEquals(false, $account->activated);
        $this->assertFalse($account->currentProvisioningToken->used);

        // /provisioning/{token}
        $this->keyAuthenticated($account)
            ->get($this->route . '/' . $account->currentProvisioningToken->token)
            ->assertStatus(400);

        $account->refresh();

        $this->assertEquals(false, $account->activated);
        $this->assertFalse($account->currentProvisioningToken->used);
    }

    public function testXLinphoneProvisioningHeader()
    {
        $this->withHeaders([
            'x-linphone-provisioning' => true,
        ])->get($this->accountRoute)->assertStatus(401);
    }

    public function testAuthenticatedWithPasswordProvisioning()
    {
        $password = Password::factory()->create();
        $password->account->generateUserApiKey();

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

    public function testUiSectionProvisioning()
    {
        $secondDomain = Space::factory()->create();

        $password = Password::factory()->create();
        $password->account->generateUserApiKey();
        $password->account->domain = $secondDomain->domain;
        $password->account->save();

        $this->keyAuthenticated($password->account)
            ->withHeaders([
                'x-linphone-provisioning' => true,
            ])
            ->get($this->accountRoute)
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('ha1')
            ->assertSee('disable_call_recordings_feature')
            ->assertSee('ui');
    }

    public function testAuthenticatedReProvisioning()
    {
        $password = Password::factory()->create();
        $password->account->display_name = "Anna O'Reily";
        $password->account->save();
        $password->account->generateUserApiKey();

        $provisioningToken = $password->account->provisioning_token;

        // Regenerate a new provisioning token from the authenticated account
        $this->keyAuthenticated($password->account)
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
        $password->account->generateUserApiKey();

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
        $response = $this->withHeaders([
            'x-linphone-provisioning' => true,
        ])->get($this->route . '/1234');
        $response->assertStatus(404);

        $password = Password::factory()->create();
        $password->account->generateUserApiKey();
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
        $admin->generateUserApiKey();

        $this->keyAuthenticated($admin)
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
        $response = $this->json('POST', '/api/accounts/auth_token')
            ->assertStatus(201)
            ->assertJson([
                'token' => true
            ]);

        $authToken = $response->json('token');

        $password = Password::factory()->create();
        $password->account->generateUserApiKey();

        $this->keyAuthenticated($password->account)
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

    public function testTokenExpiration()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();
        $expirationMinutes = 10;

        $this->keyAuthenticated($account)
            ->get('/api/accounts/me/provision')
            ->assertStatus(200)
            ->assertJson([
                'provisioning_token_expire_at' => null
            ]);

        config()->set('app.provisioning_token_expiration_minutes', $expirationMinutes);

        $this->keyAuthenticated($account)
            ->get('/api/accounts/me/provision')
            ->assertStatus(200)
            ->assertJson([
                'provisioning_token_expire_at' => $account->currentProvisioningToken->created_at->addMinutes($expirationMinutes)->toJSON()
            ]);

        $account->refresh();

        ProvisioningToken::where('id', $account->currentProvisioningToken->id)
            ->update(['created_at' => $account->currentProvisioningToken->created_at->subMinutes(1000)]);

        $this->withHeaders([
            'x-linphone-provisioning' => true,
        ])
            ->get($this->route . '/' . $account->provisioning_token)
            ->assertStatus(410);
    }

    public function testCoTURN()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();

        $host = 'coturn.tld';
        $realm = 'realm.tld';

        $this->keyAuthenticated($account)
            ->get('/api/accounts/me/services/turn')
            ->assertStatus(404);

        config()->set('app.coturn_server_host', $host);
        config()->set('app.coturn_static_auth_secret', 'secret');

        $this->keyAuthenticated($account)
            ->get('/api/accounts/me/services/turn')
            ->assertStatus(200)
            ->assertJson([
                'ttl' => config()->get('app.coturn_session_ttl_minutes') * 60
            ]);
    }
}
