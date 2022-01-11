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

use App\Password;

class AccountApiKeyTest extends TestCase
{
    use RefreshDatabase;

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
}