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
use App\AccountCreationRequestToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\AccountCreationToken;
use App\Admin;
use Carbon\Carbon;

class ApiAccountCreationTokenTest extends TestCase
{
    use RefreshDatabase;

    protected $tokenRoute = '/api/account_creation_tokens/send-by-push';
    protected $tokenRequestRoute = '/api/account_creation_request_tokens';
    protected $tokenUsingCreationTokenRoute = '/api/account_creation_tokens/using-account-creation-request-token';
    protected $accountRoute = '/api/accounts/with-account-creation-token';
    protected $adminRoute = '/api/account_creation_tokens';
    protected $method = 'POST';

    protected $pnProvider = 'provider';
    protected $pnParam = 'param';
    protected $pnPrid = 'id';

    public function testCorrectParameters()
    {
        $this->assertSame(AccountCreationToken::count(), 0);
        $this->json($this->method, $this->tokenRoute, [
            'pn_provider' => $this->pnProvider,
            'pn_param' => $this->pnParam,
            'pn_prid' => $this->pnPrid,
        ])->assertStatus(503);
    }

    public function testMandatoryParameters()
    {
        $this->json($this->method, $this->tokenRoute)->assertStatus(422);

        $this->json($this->method, $this->tokenRoute, [
            'pn_provider' => null,
            'pn_param' => null,
            'pn_prid' => null,
        ])->assertStatus(422);
    }

    public function testThrottling()
    {
        AccountCreationToken::factory()->create([
            'pn_provider' => $this->pnProvider,
            'pn_param' => $this->pnParam,
            'pn_prid' => $this->pnPrid,
        ]);

        $this->json($this->method, $this->tokenRoute, [
            'pn_provider' => $this->pnProvider,
            'pn_param' => $this->pnParam,
            'pn_prid' => $this->pnPrid,
        ])->assertStatus(503);

        // Redeem all the tokens
        AccountCreationToken::where('used', false)->update(['used' => true]);

        $this->json($this->method, $this->tokenRoute, [
            'pn_provider' => $this->pnProvider,
            'pn_param' => $this->pnParam,
            'pn_prid' => $this->pnPrid,
        ])->assertStatus(429);
    }

    public function testAdminEndpoint()
    {
        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $response = $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->adminRoute)
            ->assertStatus(201);

        $this->assertDatabaseHas('account_creation_tokens', [
            'token' => $response->json()['token']
        ]);
    }

    public function testInvalidToken()
    {
        $token = AccountCreationToken::factory()->create();

        // Invalid token
        $response = $this->json($this->method, $this->accountRoute, [
            'username' => 'username',
            'algorithm' => 'SHA-256',
            'password' => '123',
            'account_creation_token' => '0123456789abc'
        ]);
        $response->assertStatus(422);

        // Valid token
        $response = $this->json($this->method, $this->accountRoute, [
            'username' => 'username',
            'algorithm' => 'SHA-256',
            'password' => '123',
            'account_creation_token' => $token->token
        ]);
        $response->assertStatus(200);

        // Expired token
        $response = $this->json($this->method, $this->accountRoute, [
            'username' => 'username2',
            'algorithm' => 'SHA-256',
            'password' => '123',
            'account_creation_token' => $token->token
        ]);
        $response->assertStatus(422);

        $this->assertDatabaseHas('account_creation_tokens', [
            'used' => true,
            'account_id' => Account::where('username', 'username')->first()->id,
        ]);
    }

    public function testBlacklistedUsername()
    {
        $token = AccountCreationToken::factory()->create();

        config()->set('app.blacklisted_usernames', 'foobar,blacklisted,username-.*');

        // Blacklisted username
        $response = $this->json($this->method, $this->accountRoute, [
            'username' => 'blacklisted',
            'algorithm' => 'SHA-256',
            'password' => '123',
            'account_creation_token' => $token->token
        ]);
        $response->assertJsonValidationErrors(['username']);

        // Blacklisted regex username
        $response = $this->json($this->method, $this->accountRoute, [
            'username' => 'username-gnap',
            'algorithm' => 'SHA-256',
            'password' => '123',
            'account_creation_token' => $token->token
        ]);

        $response->assertJsonValidationErrors(['username']);

        // Valid username
        $response = $this->json($this->method, $this->accountRoute, [
            'username' => 'valid-username',
            'algorithm' => 'SHA-256',
            'password' => '123',
            'account_creation_token' => $token->token
        ]);

        $response->assertStatus(200);
    }

    public function testAccountCreationRequestToken()
    {
        $response = $this->json($this->method, $this->tokenRequestRoute);
        $response->assertStatus(201);
        $creationRequestToken = $response->json()['token'];

        $this->assertSame($response->json()['validation_url'], route('account.creation_request_token.check', $creationRequestToken));

        // Validate the creation request token
        AccountCreationRequestToken::where('token', $creationRequestToken)->update(['validated_at' => Carbon::now()]);

        $response = $this->json($this->method, $this->tokenUsingCreationTokenRoute, [
            'account_creation_request_token' => $creationRequestToken
        ])->assertStatus(201);

        $creationToken = $response->json()['token'];

        $this->assertDatabaseHas('account_creation_request_tokens', [
            'token' => $creationRequestToken,
            'used' => true
        ]);

        $this->assertDatabaseHas('account_creation_tokens', [
            'token' => $creationToken
        ]);

        $this->assertSame(
            AccountCreationRequestToken::where('token', $creationRequestToken)->first()->accountCreationToken->id,
            AccountCreationToken::where('token', $creationToken)->first()->id
        );
    }
}
