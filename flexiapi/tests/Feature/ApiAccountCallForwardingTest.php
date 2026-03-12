<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2026 Belledonne Communications SARL, All rights reserved.

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
use function PHPUnit\Framework\assertJson;

class ApiAccountCallForwardingTest extends TestCase
{
    protected $route = '/api/accounts/me/call_forwardings';
    protected $method = 'POST';

    public function testResolving()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();

        $uri = 'sip:uri';
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'forward_to' => 'sip_uri',
                'sip_uri' => $uri,
                'enabled' => true
            ])
            ->assertStatus(201);

        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $this->keyAuthenticated($admin)
            ->get('/api/resolve/' . $account->identifier)
            ->assertStatus(200)
            ->assertJsonFragment(['type' => 'account']);
    }

    public function testCrud()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $uri = 'sip:uri';

        // Contacts

        $contactAccount = Account::factory()->create();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'forward_to' => 'contact',
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['contact_id']);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'forward_to' => 'contact',
                'contact_id' => $contactAccount->id,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['contact_id']);

        $this->keyAuthenticated($admin)
            ->json($this->method, '/api/accounts/' . $account->id . '/contacts/' . $contactAccount->id)
            ->assertStatus(200);

        $response = $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'forward_to' => 'contact',
                'contact_id' => $contactAccount->id,
                'enabled' => true
            ])
            ->assertStatus(201);

        $this->keyAuthenticated($account)
            ->get($this->route)
            ->assertJsonFragment(['contact_sip_uri' => $contactAccount->sip_uri]);

        // SIP URI

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'forward_to' => 'sip_uri',
                'sip_uri' => null,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['type']);

        $this->keyAuthenticated($account)
            ->json('DELETE', $this->route . '/' . $response['id'])
            ->assertStatus(200);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'forward_to' => 'sip_uri',
                'sip_uri' => null,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['sip_uri']);

        $response = $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'forward_to' => 'sip_uri',
                'sip_uri' => $uri,
                'enabled' => true
            ])
            ->assertStatus(201);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'away',
                'forward_to' => 'sip_uri',
                'sip_uri' => $uri,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['enabled']);


        $this->keyAuthenticated($account)
            ->json('PUT', $this->route . '/' . $response->json()['id'], [
                'type' => 'always',
                'forward_to' => 'sip_uri',
                'sip_uri' => $uri,
                'enabled' => false
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'away',
                'forward_to' => 'sip_uri',
                'sip_uri' => $uri,
                'enabled' => true
            ])
            ->assertStatus(201);

        $this->assertCount(2, $this->keyAuthenticated($account)
            ->json('GET', $this->route)->json());
    }
}
