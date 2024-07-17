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
use App\PhoneCountry;
use App\SipDomain;
use Tests\TestCase;

class ApiPhoneCountryTest extends TestCase
{
    protected $route = '/api/phone_countries';
    protected $method = 'POST';
    protected $routeChangePhone = '/api/accounts/me/phone';

    public function testCreatePhoneByCountry()
    {
        $account = Account::factory()->withConsumedAccountCreationToken()->create();
        $account->generateApiKey();

        $frenchPhoneNumber = '+33612121212';
        $dutchPhoneNumber = '+31612121212';

        $this->get($this->route)
            ->assertStatus(200)
            ->assertJsonFragment([
                'code' => 'FR',
                'activated' => true
            ])
            ->assertJsonFragment([
                'code' => 'NL',
                'activated' => false
            ]);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->routeChangePhone.'/request', [
                'phone' => $frenchPhoneNumber
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->routeChangePhone.'/request', [
                'phone' => $dutchPhoneNumber
            ])
            ->assertJsonValidationErrors(['phone']);

        PhoneCountry::where('code', 'NL')->update(['activated' => true]);

        $this->get($this->route)
            ->assertStatus(200)
            ->assertJsonFragment([
                'code' => 'NL',
                'activated' => true
            ]);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->routeChangePhone.'/request', [
                'phone' => $dutchPhoneNumber
            ])
            ->assertStatus(200);
    }
}
