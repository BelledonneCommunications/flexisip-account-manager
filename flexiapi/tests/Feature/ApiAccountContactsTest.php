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
use App\AccountType;
use App\ContactsList;
use App\Password;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiAccountContactsTest extends TestCase
{
    protected $route = '/api/accounts';
    protected $contactsListsRoute = '/api/contacts_lists';
    protected $method = 'POST';

    public function testCreate()
    {
        $password1 = Password::factory()->create();
        $password2 = Password::factory()->create();
        $password3 = Password::factory()->create();

        $typeKey = 'phone';
        $actionKey = '123';
        $actionCode = '123';

        $admin = Account::factory()->admin()->create();
        $admin->generateApiKey();

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route . '/' . $password1->account->id . '/contacts/' . $password2->account->id)
            ->assertStatus(200);

        $this->assertEquals(1, DB::table('contacts')->count());

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route . '/' . $password1->account->id . '/contacts/' . $password3->account->id)
            ->assertStatus(200);

        $this->assertEquals(2, DB::table('contacts')->count());

        // Type
        $this->keyAuthenticated($admin)
            ->json($this->method, '/api/account_types', [
                'key' => $typeKey,
            ])
            ->assertStatus(201);

        $accountType = AccountType::first();

        $this->keyAuthenticated($admin)
            ->json($this->method, '/api/accounts/' . $password2->account->id . '/types/' . $accountType->id)
            ->assertStatus(200);

        // Action
        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route . '/' . $password2->account->id . '/actions', [
                'key' => $actionKey,
                'code' => $actionCode
            ]);

        // Retry
        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route . '/' . $password1->account->id . '/contacts/' . $password2->account->id)
            ->assertStatus(403);
        $this->assertEquals(2, DB::table('contacts')->count());

        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $password1->account->id . '/contacts')
            ->assertJson([
                [
                    'id' => $password2->account->id
                ]
            ]);

        // /me
        $password1->account->generateApiKey();
        $password1->account->save();

        $this->keyAuthenticated($password1->account)
            ->get($this->route . '/me/contacts')
            ->assertStatus(200)
            ->assertJson([[
                'username' => $password2->account->username,
                'activated' => true
            ]]);

        $this->keyAuthenticated($password1->account)
            ->get($this->route . '/me/contacts/' . $password2->account->identifier)
            ->assertStatus(200)
            ->assertJson([
                'username' => $password2->account->username,
                'activated' => true
            ]);

        // Vcard 4.0
        $this->keyAuthenticated($password1->account)
            ->get('/contacts/vcard')
            ->assertStatus(200)
            ->assertSeeText("FN:" . $password2->display_name)
            ->assertSeeText("X-LINPHONE-ACCOUNT-TYPE:" . $typeKey)
            ->assertSeeText("X-LINPHONE-ACCOUNT-DTMF-PROTOCOL:" . $password2->dtmf_protocol)
            ->assertSeeText("X-LINPHONE-ACCOUNT-ACTION:" . $actionKey . ';' . $actionCode);

        $this->keyAuthenticated($password1->account)
            ->get('/contacts/vcard/' . $password2->account->identifier)
            ->assertStatus(200)
            ->assertSeeText("X-LINPHONE-ACCOUNT-TYPE:" . $typeKey)
            ->assertSeeText("X-LINPHONE-ACCOUNT-DTMF-PROTOCOL:" . $password2->dtmf_protocol)
            ->assertSeeText("X-LINPHONE-ACCOUNT-ACTION:" . $actionKey . ';' . $actionCode);

        $this->keyAuthenticated($password1->account)
            ->get($this->route . '/me/contacts/' . $password2->account->identifier)
            ->assertStatus(200)
            ->assertJson([
                'username' => $password2->account->username,
                'activated' => true
            ]);

        // Remove
        $this->keyAuthenticated($admin)
            ->delete($this->route . '/' . $password1->account->id . '/contacts/' . $password2->account->id)
            ->assertStatus(200);

        $this->assertEquals(1, DB::table('contacts')->count());

        // Retry
        $this->keyAuthenticated($admin)
            ->delete($this->route . '/' . $password1->account->id . '/contacts/' . $password2->account->id)
            ->assertStatus(403);
        $this->assertEquals(1, DB::table('contacts')->count());

        /**
         * Contacts lists
         *
         */

        // Create the Contacts list
        $contactsListsTitle = 'Contacts List title';

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->contactsListsRoute, [
                'title' => $contactsListsTitle,
                'description' => 'Description'
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('contacts_lists', [
            'title' => $contactsListsTitle
        ]);

        // Attach the Contacts and the Contacts List

        $contactsList = ContactsList::first();

        $this->keyAuthenticated($admin)
            ->post($this->contactsListsRoute . '/' . $contactsList->id . '/contacts/' . $password1->account->id)
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->post($this->contactsListsRoute . '/' . $contactsList->id . '/contacts/' . $password2->account->id)
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->post($this->contactsListsRoute . '/' . $contactsList->id . '/contacts/' . $password3->account->id)
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->post($this->contactsListsRoute . '/' . $contactsList->id . '/contacts/1234')
            ->assertStatus(404);

        $this->keyAuthenticated($admin)
            ->post($this->route . '/' . $admin->id . '/contacts_lists/' . $contactsList->id)
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->post($this->route . '/' . $admin->id . '/contacts_lists/' . $contactsList->id)
            ->assertStatus(403);

        // Get the contacts and vcards

        $this->keyAuthenticated($admin)
            ->get($this->route . '/me/contacts')
            ->assertStatus(200)
            ->assertJsonFragment([
                'username' => $password1->account->username,
                'activated' => true
            ])
            ->assertJsonFragment([
                'username' => $password2->account->username,
                'activated' => true
            ])
            ->assertJsonFragment([
                'username' => $password3->account->username,
                'activated' => true
            ]);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/me/contacts/' . $password2->account->identifier)
            ->assertStatus(200)
            ->assertJsonFragment([
                'username' => $password2->account->username,
                'activated' => true
            ]);

        $this->keyAuthenticated($admin)
            ->get('/contacts/vcard')
            ->assertStatus(200)
            ->assertSeeText("FN:" . $password1->display_name)
            ->assertSeeText("FN:" . $password2->display_name)
            ->assertSeeText("FN:" . $password3->display_name);

        $this->keyAuthenticated($admin)
            ->get('/contacts/vcard/' . $password2->account->identifier)
            ->assertStatus(200)
            ->assertSeeText("FN:" . $password2->display_name);
    }
}
