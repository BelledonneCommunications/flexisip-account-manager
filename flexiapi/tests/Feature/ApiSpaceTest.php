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
use App\Space;
use Carbon\Carbon;
use Tests\TestCase;

class ApiSpaceTest extends TestCase
{
    protected $method = 'POST';
    protected $route = '/api/spaces';
    protected $accountRoute = '/api/accounts';

    public function testBaseAdmin()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateApiKey();

        $secondDomain = Space::factory()->secondDomain()->create();
        $username = 'foo';

        // Admin domain
        $this->keyAuthenticated($admin)
            ->json($this->method, $this->accountRoute, [
                'username' => $username,
                'domain' => $admin->domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertStatus(200);

        // Second domain
        $this->keyAuthenticated($admin)
            ->json($this->method, $this->accountRoute, [
                'username' => $username,
                // The domain is ignored there, to fallback on the admin one
                'domain' => $secondDomain->domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertJsonValidationErrors(['username']);

        // Admin domain is now a super domain
        Space::where('domain', $admin->domain)->update(['super' => true]);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->accountRoute, [
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

        $response = $this->keyAuthenticated($admin)
            -> json($this->method, $this->route, [
                'domain' => $thirdDomain,
                'host' => $thirdDomain,
                'super' => false
            ])
            ->assertStatus(201);

        $this->keyAuthenticated($admin)
            ->json('GET', $this->route)
            ->assertJsonFragment([
                'domain' => $thirdDomain,
                'host' => $thirdDomain,
                'super' => false
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('PUT', $this->route . '/' . $thirdDomain, [
                'super' => true
            ])
            ->assertStatus(422);

        $json = $response->json();
        $json['super'] = true;
        $json['hide_settings'] = true;

        $this->keyAuthenticated($admin)
            ->json('PUT', $this->route . '/' . $thirdDomain, $json)
            ->assertJsonFragment([
                'domain' => $thirdDomain,
                'host' => $thirdDomain,
                'super' => true,
                'hide_settings' => true
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
                'host' => $admin->domain,
                'super' => true,
                'max_accounts' => 0,
                'expire_at' => null
            ])
            ->assertStatus(200);
    }

    public function testUserCreation()
    {
        $admin = Account::factory()->superAdmin()->create();
        $admin->generateApiKey();

        $domain = 'domain.com';

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->accountRoute, [
                'username' => 'first',
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])->assertStatus(403);

        $this->keyAuthenticated($admin)
            -> json($this->method, $this->route, [
                'domain' => $domain,
                'host' => $domain,
                'super' => false,
                'max_accounts' => 1
            ])->assertStatus(201);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->accountRoute, [
                'username' => 'first',
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->accountRoute, [
                'username' => 'second',
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])->assertStatus(403);
    }
}
