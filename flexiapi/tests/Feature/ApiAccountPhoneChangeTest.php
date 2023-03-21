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
use App\PhoneChangeCode;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAccountPhoneChangeTest extends TestCase
{
    use RefreshDatabase;

    protected $route = '/api/accounts/me/phone';
    protected $method = 'POST';

    public function testRequest()
    {
        $password = Password::factory()->create();
        $password->account->generateApiKey();

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route.'/request', [
                'phone' => 'blabla'
            ])
            ->assertStatus(422);

        // Send a SMS
        /*$this->keyAuthenticated($password->account)
            ->json($this->method, $this->route.'/request', [
                'phone' => '+3312345678'
            ])
            ->assertStatus(200);*/
    }

    public function testConfirmLongCode()
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

        $this->keyAuthenticated($phoneChange->account)
            ->get('/api/accounts/me')
            ->assertStatus(200)
            ->assertJson([
                'phone' => null
            ]);

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
    }
}
