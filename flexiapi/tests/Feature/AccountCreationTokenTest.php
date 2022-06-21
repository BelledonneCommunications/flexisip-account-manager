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

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\AccountCreationToken;

class AccountCreationTokenTest extends TestCase
{
    use RefreshDatabase;

    protected $tokenRoute = '/api/account_creation_tokens/send-by-push';
    protected $accountRoute = '/api/accounts/with-account-creation-token';
    protected $method = 'POST';

    protected $pnProvider = 'provider';
    protected $pnParam = 'param';
    protected $pnPrid = 'id';

    public function testMandatoryParameters()
    {
        $response = $this->json($this->method, $this->tokenRoute);
        $response->assertStatus(422);
    }

    public function testCorrectParameters()
    {
        $response = $this->json($this->method, $this->tokenRoute, [
            'pn_provider' => $this->pnProvider,
            'pn_param' => $this->pnParam,
            'pn_prid' => $this->pnPrid,
        ]);
        $response->assertStatus(503);
    }

    public function testLimit()
    {
        $token = AccountCreationToken::factory()->create();

        $response = $this->json($this->method, $this->tokenRoute, [
            'pn_provider' => $token->pn_provider,
            'pn_param' => $token->pn_param,
            'pn_prid' => $token->pn_prid,
        ]);
        $response->assertStatus(403);
    }

    public function testInvalidToken()
    {
        $token = AccountCreationToken::factory()->create();

        // Invalid token
        $response = $this->json($this->method, $this->accountRoute, [
            'username' => 'username',
            'algorithm' => 'SHA-256',
            'password' => '2',
            'account_creation_token' => '0123456789abc'
        ]);
        $response->assertStatus(422);

        // Valid token
        $response = $this->json($this->method, $this->accountRoute, [
            'username' => 'username',
            'algorithm' => 'SHA-256',
            'password' => '2',
            'account_creation_token' => $token->token
        ]);
        $response->assertStatus(200);

        // Expired token
        $response = $this->json($this->method, $this->accountRoute, [
            'username' => 'username2',
            'algorithm' => 'SHA-256',
            'password' => '2',
            'account_creation_token' => $token->token
        ]);
        $response->assertStatus(422);
    }
}