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
use App\SipDomain;
use Tests\TestCase;

class ApiSipDomainTest extends TestCase
{
    protected $route = '/api/sip_domains';

    public function testBaseAdmin()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateApiKey();

        $secondDomain = SipDomain::factory()->secondDomain()->create();
        $username = 'foo';

        // Admin domain
        $this->keyAuthenticated($admin)
            ->json('POST', '/api/accounts', [
                'username' => $username,
                'domain' => $admin->domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertStatus(200);

        // Second domain
        $this->keyAuthenticated($admin)
            ->json('POST', '/api/accounts', [
                'username' => $username,
                // The domain is ignored there, to fallback on the admin one
                'domain' => $secondDomain->domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertJsonValidationErrors(['username']);

        // Admin domain is now a super domain
        SipDomain::where('domain', $admin->domain)->update(['super' => true]);

        $this->keyAuthenticated($admin)
            ->json('POST', '/api/accounts', [
                'username' => $username,
                'domain' => $secondDomain->domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertStatus(200);
    }

    public function testSuperAdmin()
    {
        $admin = Account::factory()->superAdmin()->create();
        $admin->generateApiKey();

        $thirdDomain = 'third.domain';

        $this->keyAuthenticated($admin)
            -> json('POST', $this->route, [
                'domain' => $thirdDomain,
                'super' => false
            ])
            ->assertStatus(201);

        $this->keyAuthenticated($admin)
            ->json('GET', $this->route)
            ->assertJsonFragment([
                'domain' => $thirdDomain,
                'super' => false
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('PUT', $this->route . '/' . $thirdDomain, [
                'super' => true
            ])
            ->assertJsonFragment([
                'domain' => $thirdDomain,
                'super' => true
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('DELETE', $this->route . '/' . $thirdDomain)
            ->assertStatus(200);

        // Only the admin domain remains
        $this->keyAuthenticated($admin)
            ->json('GET', $this->route)
            ->assertJsonFragment([
                'domain' => $admin->domain,
                'super' => true
            ])
            ->assertStatus(200);
    }
}
