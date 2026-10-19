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
use App\Space;
use Tests\TestCase;

class ApiAccountCallForwardingTest extends TestCase
{
    protected $route = '/api/accounts/me/call_forwardings';
    protected $method = 'POST';

    public function testResolving()
    {
        $account = Account::factory()->create();

        $voicemail = Account::factory()->create();
        $account->space->voicemail_account_id = $voicemail->id;
        $account->space->save();

        $account->generateUserApiKey();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'voicemail' => true,
                'enabled' => true
            ])
            ->assertCreated();

        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $this->keyAuthenticated($admin)
            ->get('/api/resolve/' . $account->identifier)
            ->assertOk()
            ->assertJsonFragment(['type' => 'account']);
    }

    public function testCrudTypes()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();

        $admin = Account::factory()->admin()->create();

        // Types

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'busy',
                'sip_account_id' => $admin->id,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['type']);

        $response = $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'sip_account_id' => $admin->id,
                'enabled' => true
            ])
            ->assertCreated();

        $this->keyAuthenticated($account)
            ->json('PUT', $this->route . '/' . $response->json()['id'], [
                'type' => 'busy',
                'sip_account_id' => $admin->id,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['type']);
    }

    public function testCrudNoTarget()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();

        // No target

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'enabled' => true
            ])
            ->assertStatus(422);
    }

    public function testCrudVoicemail()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();

        $admin = Account::factory()->admin()->create();

        // Voicemail

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'voicemail' => true,
                'enabled' => true
            ])
            ->assertStatus(422);

        $voicemail = Account::factory()->create();
        $account->space->voicemail_account_id = $voicemail->id;
        $account->space->save();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'voicemail' => true,
                'enabled' => true
            ])
            ->assertCreated();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'no_answer',
                'voicemail' => true,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['voicemail']);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'voicemail' => true,
                'sip_account_id' => $admin->id,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['voicemail', 'sip_account_id']);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'voicemail' => true,
                'contact_account_id' => $admin->id,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['voicemail', 'contact_account_id']);
    }

    public function testCrudContacts()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();

        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $voicemail = Account::factory()->create();
        $account->space->voicemail_account_id = $voicemail->id;
        $account->space->save();

        $voicemailResponse = $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'voicemail' => true,
                'enabled' => true
            ])
            ->assertCreated();

        // Contacts

        $contactAccount = Account::factory()->create();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'contact_account_id' => 123,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['contact_account_id']);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'contact_account_id' => $contactAccount->id,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['contact_account_id']);

        $this->keyAuthenticated($admin)
            ->json($this->method, '/api/accounts/' . $account->id . '/contacts/' . $contactAccount->id)
            ->assertOk();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'contact_account_id' => $contactAccount->id,
                'enabled' => true
            ])
            ->assertJsonValidationErrorFor('type');

        $this->keyAuthenticated($account)
            ->json('DELETE', $this->route . '/' . $voicemailResponse['id'])
            ->assertOk();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'contact_account_id' => $contactAccount->id,
                'enabled' => true
            ])
            ->assertCreated();

        $this->keyAuthenticated($account)
            ->get($this->route)
            ->assertJsonFragment(['contact_sip_uri' => $contactAccount->sip_uri]);
    }

    public function testCrudSipUri()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();

        $admin = Account::factory()->admin()->create();

        $secondSpace = Space::factory()->secondDomain()->create();
        $otherDomainAccount = Account::factory()->fromSpace($secondSpace)->create();

        // SIP URI

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'sip_account_id' => 123,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['sip_account_id']);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'sip_account_id' => $account->id,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['sip_account_id']);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'sip_account_id' => $otherDomainAccount->id,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['sip_account_id']);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'sip_account_id' => $admin->id,
                'contact_account_id' => $admin->id,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['sip_account_id', 'contact_account_id']);

        $response = $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'sip_account_id' => $admin->id,
                'enabled' => true
            ])
            ->assertCreated();

        $this->keyAuthenticated($account)
            ->json('PUT', $this->route . '/' . $response->json()['id'], [
                'type' => 'always',
                'enabled' => true
            ])
            ->assertStatus(422);

        $this->keyAuthenticated($account)
            ->json('PUT', $this->route . '/' . $response->json()['id'], [
                'type' => 'always',
                'sip_account_id' => $account->id,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['sip_account_id']);
    }

    public function testCrudEnablingConflicts()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();

        $admin = Account::factory()->admin()->create();

        // Enabling conflicts

        $response = $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'always',
                'sip_account_id' => $admin->id,
                'enabled' => true
            ])
            ->assertCreated();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'no_answer',
                'sip_account_id' => $admin->id,
                'enabled' => true
            ])
            ->assertJsonValidationErrors(['enabled']);

        $this->keyAuthenticated($account)
            ->json('PUT', $this->route . '/' . $response->json()['id'], [
                'type' => 'always',
                'sip_account_id' => $admin->id,
                'enabled' => false
            ])
            ->assertOk();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'type' => 'no_answer',
                'sip_account_id' => $admin->id,
                'enabled' => true
            ])
            ->assertCreated();

        $this->assertCount(2, $this->keyAuthenticated($account)
            ->json('GET', $this->route)->json());
    }
}
