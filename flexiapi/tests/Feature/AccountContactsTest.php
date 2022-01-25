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

use App\Password;
use App\AccountAction;
use App\AccountType;
use App\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccountContactTest extends TestCase
{
    use RefreshDatabase;

    protected $route = '/api/accounts';
    protected $method = 'POST';

    public function testCreate()
    {
        $password1 = Password::factory()->create();
        $password2 = Password::factory()->create();
        $password3 = Password::factory()->create();

        $typeKey = 'phone';
        $actionKey = '123';
        $actionCode = '123';

        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route.'/'.$password1->account->id.'/contacts/'.$password2->account->id)
            ->assertStatus(200);

        $this->assertEquals(1, DB::table('contacts')->count());

        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route.'/'.$password1->account->id.'/contacts/'.$password3->account->id)
            ->assertStatus(200);

        $this->assertEquals(2, DB::table('contacts')->count());

        // Type
        $this->keyAuthenticated($admin->account)
            ->json($this->method, '/api/account_types', [
                'key' => $typeKey,
            ])
            ->assertStatus(201);

        $accountType = AccountType::first();

        $this->keyAuthenticated($admin->account)
            ->json($this->method, '/api/accounts/'.$password2->account->id.'/types/'.$accountType->id)
            ->assertStatus(200);

        // Action
        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route.'/'.$password2->account->id.'/actions', [
                'key' => $actionKey,
                'code' => $actionCode
            ]);

        // Retry
        $this->keyAuthenticated($admin->account)
             ->json($this->method, $this->route.'/'.$password1->account->id.'/contacts/'.$password2->account->id)
             ->assertStatus(403);
        $this->assertEquals(2, DB::table('contacts')->count());

        $this->keyAuthenticated($admin->account)
             ->get($this->route.'/'.$password1->account->id.'/contacts')
             ->assertJson([
                [
                    'id' => $password2->account->id
                ]
             ]);

        // /me
        $password1->account->generateApiKey();
        $password1->account->save();

        $this->keyAuthenticated($password1->account)
             ->get($this->route.'/me/contacts')
             ->assertStatus(200)
             ->assertJson([[
                'username' => $password2->account->username,
                'activated' => true
             ]]);

        $this->keyAuthenticated($password1->account)
             ->get($this->route.'/me/contacts/'.$password2->account->identifier)
             ->assertStatus(200)
             ->assertJson([
                'username' => $password2->account->username,
                'activated' => true
             ]);

        // Vcard 4.0
        $this->keyAuthenticated($password1->account)
             ->get('/contacts/vcard')
             ->assertStatus(200)
             ->assertSeeText("X-LINPHONE-ACCOUNT-TYPE:".$typeKey)
             ->assertSeeText("X-LINPHONE-ACCOUNT-DTMF-PROTOCOL:".$password2->dtmf_protocol)
             ->assertSeeText("X-LINPHONE-ACCOUNT-ACTION:".$actionKey.';'.$actionCode);

        $this->keyAuthenticated($password1->account)
             ->get('/contacts/vcard/'.$password2->account->identifier)
             ->assertStatus(200)
             ->assertSeeText("X-LINPHONE-ACCOUNT-TYPE:".$typeKey)
             ->assertSeeText("X-LINPHONE-ACCOUNT-DTMF-PROTOCOL:".$password2->dtmf_protocol)
             ->assertSeeText("X-LINPHONE-ACCOUNT-ACTION:".$actionKey.';'.$actionCode);

        $this->keyAuthenticated($password1->account)
             ->get($this->route.'/me/contacts/'.$password2->account->identifier)
             ->assertStatus(200)
             ->assertJson([
                'username' => $password2->account->username,
                'activated' => true
             ]);

        // Remove
        $this->keyAuthenticated($admin->account)
             ->delete($this->route.'/'.$password1->account->id.'/contacts/'.$password2->account->id)
             ->assertStatus(200);

        $this->assertEquals(1, DB::table('contacts')->count());

        // Retry
        $this->keyAuthenticated($admin->account)
             ->delete($this->route.'/'.$password1->account->id.'/contacts/'.$password2->account->id)
             ->assertStatus(403);
        $this->assertEquals(1, DB::table('contacts')->count());
    }
}
