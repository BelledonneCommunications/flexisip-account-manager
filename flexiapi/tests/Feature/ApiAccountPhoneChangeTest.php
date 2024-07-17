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
use App\PhoneChangeCode;
use App\PhoneCountry;
use Tests\TestCase;

class ApiAccountPhoneChangeTest extends TestCase
{
    protected $route = '/api/accounts/me/phone';
    protected $method = 'POST';

    public function testRequest()
    {
        $account = Account::factory()->withConsumedAccountCreationToken()->create();
        $account->generateApiKey();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/request', [
                'phone' => 'blabla'
            ])
            ->assertStatus(422);

        // Send a SMS
        /*$this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/request', [
                'phone' => '+3312345678'
            ])
            ->assertStatus(200);*/
    }

    public function testCodeExpiration()
    {
        $account = Account::factory()->withConsumedAccountCreationToken()->create();
        $account->generateApiKey();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/request', [
                'phone' => '+33612312312'
            ])
            ->assertStatus(200);

        config()->set('app.phone_change_code_expiration_minutes', 10);

        PhoneChangeCode::where('id', $account->phoneChangeCode->id)
            ->update(['created_at' => $account->phoneChangeCode->created_at->subMinutes(1000)]);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'code' => $account->phoneChangeCode->code
            ])
            ->assertStatus(410);
    }

    public function testCreatePhoneByCountry()
    {
        $account = Account::factory()->withConsumedAccountCreationToken()->create();
        $account->generateApiKey();

        $frenchPhoneNumber = '+33612121212';
        $dutchPhoneNumber = '+31612121212';

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/request', [
                'phone' => $frenchPhoneNumber
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/request', [
                'phone' => $dutchPhoneNumber
            ])
            ->assertJsonValidationErrors(['phone']);

        PhoneCountry::where('code', 'NL')->update(['activated' => true]);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/request', [
                'phone' => $dutchPhoneNumber
            ])
            ->assertStatus(200);
    }

    public function testUnvalidatedAccount()
    {
        $account = Account::factory()->create();
        $account->generateApiKey();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/request', [
                'phone' => 'blabla'
            ])
            ->assertStatus(403);
    }

    public function testConfirmWrongCode()
    {
        $phoneChange = PhoneChangeCode::factory()->create();

        $this->keyAuthenticated($phoneChange->account)
            ->json($this->method, $this->route, [
                'code' => 'wrong'
            ])
            ->assertStatus(422);
    }

    public function testConfirmGoodCode()
    {
        $phoneChange = PhoneChangeCode::factory()->create();
        $phone = $phoneChange->phone;

        $admin = Account::factory()->admin()->create();
        $admin->generateApiKey();

        $this->keyAuthenticated($phoneChange->account)
            ->get('/api/accounts/me')
            ->assertStatus(200)
            ->assertJson([
                'phone' => null
            ]);

        // Check who can see the code
        $this->keyAuthenticated($admin)
            ->json('GET', '/api/accounts/' . $phoneChange->account->id)
            ->assertStatus(200)
            ->assertSee($phoneChange->code);

        $this->keyAuthenticated($phoneChange->account)
            ->json('GET', '/api/accounts/me')
            ->assertStatus(200)
            ->assertDontSee($phoneChange->code);

        $this->keyAuthenticated($phoneChange->account)
            ->json($this->method, $this->route, [
                'code' => $phoneChange->code
            ])
            ->assertStatus(200)
            ->assertJson([
                'phone' => $phone,
            ]);

        $this->keyAuthenticated($phoneChange->account)
            ->get('/api/accounts/me')
            ->assertStatus(200)
            ->assertJson([
                'phone' => $phone
            ]);

        // Check that the code is gone
        $this->keyAuthenticated($admin)
            ->json('GET', '/api/accounts/' . $phoneChange->account->id)
            ->assertStatus(200)
            ->assertDontSee($phoneChange->code);
    }
}
