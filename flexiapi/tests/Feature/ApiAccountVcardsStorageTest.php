<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2024 Belledonne Communications SARL, All rights reserved.

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

class ApiVcardsStorageTest extends TestCase
{
    protected $route = '/api/accounts/me/vcards-storage';
    protected $method = 'POST';

    public function testAccountCrud()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateApiKey();

        $account = Account::factory()->create();
        $account->generateApiKey();

        $adminRoute = '/api/accounts/' . $account->id . '/vcards-storage';

        $uid = 'urn:uuid:f81d4fae-7dec-11d0-a765-00a0c91e6bf6';
        $lastVcard =
'BEGIN:VCARD
VERSION:4.0
FN:Jhonny English
UID:' . $uid . '
END:VCARD
';
        $uid2 = 'urn:uuid:a5b33443-687c-4d19-bdd0-b30cf76bf96d';
        $secondVcard =
'BEGIN:VCARD
VERSION:4.0
FN:Simone Perreault
UID:' . $uid2 . '
END:VCARD
';
        $uid3 = 'urn:uuid:a5b33443-687c-4d19-bdd0-b30cf76bfc4d';
        $thirdVcard =
'BEGIN:VCARD
VERSION:4.0
FN:Jean Jannot
UID:' . $uid3 . '
END:VCARD
';

        // Missing vcard
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'foo' => 'bar'
            ])
            ->assertJsonValidationErrors(['vcard']);

        // Admin vcard
        $this->keyAuthenticated($admin)
            ->json($this->method, $adminRoute, [
                'foo' => 'bar'
            ])
            ->assertJsonValidationErrors(['vcard']);

        // Missing UID
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'vcard' =>
'BEGIN:VCARD
VERSION:4.0
FN:Simon Perreault
END:VCARD'
            ])->assertJsonValidationErrors(['vcard']);

        // Create
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'vcard' =>
'BEGIN:VCARD
VERSION:4.0
FN:Simon Perreault
UID:' . $uid . '
END:VCARD'
            ])->assertStatus(200);

        // Admin create
        $this->keyAuthenticated($admin)
            ->json($this->method, $adminRoute, [
                'vcard' => $thirdVcard])
            ->assertStatus(200);

        // Again...
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'vcard' =>
'BEGIN:VCARD
VERSION:4.0
FN:Simon Perreault
UID:' . $uid . '
END:VCARD'
            ])->assertStatus(409);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'vcard' => $secondVcard
            ])->assertStatus(200);

        $this->assertDatabaseHas('vcards_storage', [
            'uuid' => $uid
        ]);

        $this->assertDatabaseHas('vcards_storage', [
            'uuid' => $uid2
        ]);

        $this->assertDatabaseHas('vcards_storage', [
            'uuid' => $uid3
        ]);

        // Update
        $this->keyAuthenticated($account)
            ->json('PUT', $this->route . '/' . $uid, [
                'vcard' => $lastVcard
            ])->assertStatus(200);

        // Update with wrong UID
        $this->keyAuthenticated($account)
            ->json('PUT', $this->route . '/' . $uid, [
                'vcard' =>
'BEGIN:VCARD
VERSION:4.0
FN:Simone Perreault
UID:123
END:VCARD'
            ])->assertStatus(422);

        // Index
        $this->keyAuthenticated($account)
            ->get($this->route)
            ->assertStatus(200)
            ->assertJson([
                $uid => ['vcard' => $lastVcard],
                $uid2 => ['vcard' => $secondVcard]
            ]);

        // Get
        $this->keyAuthenticated($account)
            ->get($this->route . '/' . $uid)
            ->assertStatus(200)
            ->assertJson([
                'vcard' => $lastVcard
            ]);

        // Admin get
        $this->keyAuthenticated($admin)
            ->get($adminRoute . '/' . $uid)
            ->assertStatus(200)
            ->assertJson([
                'vcard' => $lastVcard
            ]);

        // Vcard format endpoints
        /*$this->keyAuthenticated($account)
            ->get('vcards-storage')
            ->assertStatus(404);*/

        $this->keyAuthenticated($account)
            /*->withHeaders([
                'content-type' => 'text/vcard',
                'accept' => 'text/vcard',
            ])*/
            ->get('vcards-storage')
            ->assertStatus(200)
            ->assertSee($lastVcard)
            ->assertSee($secondVcard);

        $this->keyAuthenticated($account)
            /*->withHeaders([
                'content-type' => 'text/vcard',
                'accept' => 'text/vcard',
            ])*/
            ->get('vcards-storage/' . $uid)
            ->assertStatus(200)
            ->assertSee($lastVcard);

        // Delete
        $this->keyAuthenticated($account)
            ->delete($this->route . '/' . $uid)
            ->assertStatus(200);

        $this->keyAuthenticated($account)
            ->get($this->route . '/' . $uid)
            ->assertStatus(404);
    }
}
