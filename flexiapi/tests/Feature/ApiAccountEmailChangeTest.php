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
use App\EmailChangeCode;
use Tests\TestCase;

class ApiAccountEmailChangeTest extends TestCase
{
    protected $route = '/api/accounts/me/email';
    protected $method = 'POST';

    public function testRequest()
    {
        $account = Account::factory()->withConsumedAccountCreationToken()->create();
        $account->generateApiKey();
        $otherAccount = Account::factory()->withEmail()->create();
        $account->generateApiKey();
        $newEmail = 'test@test.com';

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/request', [
                'email' => 'blabla'
            ])
            ->assertStatus(422);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/request', [
                'email' => $newEmail
            ])
            ->assertStatus(200);

        // Same email
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/request', [
                'email' => $account->email
            ])
            ->assertStatus(422);

        $this->keyAuthenticated($account)
            ->get('/api/accounts/me')
            ->assertStatus(200)
            ->assertJson([
                'username' => $account->username,
                'email_change_code' => [
                    'email' => $newEmail
                ]
            ]);

        // Email already exists
        config()->set('app.account_email_unique', true);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route . '/request', [
                'email' => $otherAccount->email
            ])->assertJsonValidationErrors(['email']);
    }

    public function testUnvalidatedAccount()
    {
        $account = Account::factory()->create();
        $account->generateApiKey();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/request', [
                'email' => 'test@test.com'
            ])
            ->assertStatus(403);
    }

    public function testConfirmWrongCode()
    {
        $emailChange = EmailChangeCode::factory()->create();

        $this->keyAuthenticated($emailChange->account)
            ->json($this->method, $this->route, [
                'code' => 'wrong'
            ])
            ->assertStatus(422);
    }

    public function testConfirmGoodCode()
    {
        $emailChange = EmailChangeCode::factory()->create();
        $email = $emailChange->email;

        $admin = Account::factory()->admin()->create();
        $admin->generateApiKey();

        $this->keyAuthenticated($emailChange->account)
            ->get('/api/accounts/me')
            ->assertStatus(200)
            ->assertJson([
                'email' => null
            ]);

        // Check who can see the code
        $this->keyAuthenticated($admin)
            ->json('GET', '/api/accounts/' . $emailChange->account->id)
            ->assertStatus(200)
            ->assertSee($emailChange->code);

        $this->keyAuthenticated($emailChange->account)
            ->json('GET', '/api/accounts/me')
            ->assertStatus(200)
            ->assertDontSee($emailChange->code);

        $this->keyAuthenticated($emailChange->account)
            ->json($this->method, $this->route, [
                'code' => $emailChange->code
            ])
            ->assertStatus(200)
            ->assertJson([
                'email' => $email,
            ]);

        $this->keyAuthenticated($emailChange->account)
            ->get('/api/accounts/me')
            ->assertStatus(200)
            ->assertJson([
                'email' => $email
            ]);

        // Check that the code is gone
        $this->keyAuthenticated($admin)
            ->json('GET', '/api/accounts/' . $emailChange->account->id)
            ->assertStatus(200)
            ->assertDontSee($emailChange->code);
    }
}
