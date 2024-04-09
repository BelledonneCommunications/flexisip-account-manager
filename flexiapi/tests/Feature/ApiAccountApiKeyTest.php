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
use App\ApiKey;
use App\Password;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiAccountApiKeyTest extends TestCase
{
    protected $route = '/api/accounts/me/api_key';
    protected $method = 'GET';

    public function testRefresh()
    {
        $password = Password::factory()->create();

        $response0 = $this->generateFirstResponse($password);
        $response1 = $this->generateSecondResponse($password, $response0)
                          ->get($this->route);

        // Get the API Key using the DIGEST method
        $password->account->refresh();

        $response1->assertStatus(200)
                  ->assertSee($password->account->apiKey->key)
                  ->assertPlainCookie('x-api-key', $password->account->apiKey->key);

        // Get it again using the key authenticated method
        $response2 = $this->keyAuthenticated($password->account)
                          ->get($this->route);

        $password->account->refresh();

        $response2->assertStatus(200)
                  ->assertSee($password->account->apiKey->key)
                  ->assertPlainCookie('x-api-key', $password->account->apiKey->key);
    }

    public function testRequest()
    {
        $account = Account::factory()->create();
        $account->generateApiKey();

        $this->keyAuthenticated($account)
            ->json($this->method, '/api/accounts/me')
            ->assertStatus(200);

        $this->keyAuthenticated($account)
            ->json($this->method, '/api/accounts/me')
            ->assertStatus(200);

        $this->assertDatabaseHas('api_keys', [
            'account_id' => $account->id,
            'requests' => 2
        ]);

        DB::table('api_keys')->update(['ip' => 'no_localhost']);

        $this->keyAuthenticated($account)
            ->json($this->method, '/api/accounts/me')
            ->assertStatus(401);
    }

    public function testAuthToken()
    {
        // Generate a public auth_token
        $response = $this->json('POST', '/api/accounts/auth_token')
            ->assertStatus(201)
            ->assertJson([
                'token' => true
            ]);

        $authToken = $response->json('token');

        // Try to retrieve an API key from the un-attached auth_token
        $response = $this->json($this->method, $this->route . '/' . $authToken)
            ->assertStatus(404);

        // Attach the auth_token to the account
        $password = Password::factory()->create();
        $password->account->generateApiKey();

        $this->keyAuthenticated($password->account)
            ->json($this->method, '/api/accounts/auth_token/' . $authToken . '/attach')
            ->assertStatus(200);

        // Re-attach
        $this->keyAuthenticated($password->account)
            ->json($this->method, '/api/accounts/auth_token/' . $authToken . '/attach')
            ->assertStatus(404);

        // Attach using a wrong auth_token
        $this->keyAuthenticated($password->account)
            ->json($this->method, '/api/accounts/auth_token/wrong_token/attach')
            ->assertStatus(404);

        // Retrieve an API key from the attached auth_token
        $response = $this->json($this->method, $this->route . '/' . $authToken)
            ->assertStatus(200)
            ->assertJson([
                'api_key' => true
            ]);

        $apiKey = $response->json('api_key');

        // Re-retrieve
        $this->json($this->method, $this->route . '/' . $authToken)
            ->assertStatus(404);

        // Check the if the API key can be used for the account
        $response = $this->withHeaders(['x-api-key' => $apiKey])
            ->json($this->method, '/api/accounts/me')
            ->assertStatus(200);

        // Try with a wrong From
        $response = $this->withHeaders([
                'x-api-key' => $apiKey,
                'From' => 'sip:baduser@server.tld'
            ])
            ->json($this->method, '/api/accounts/me')
            ->assertStatus(200);

        // Check if the account was correctly attached
        $this->assertEquals($response->json('email'), $password->account->email);
    }
}
