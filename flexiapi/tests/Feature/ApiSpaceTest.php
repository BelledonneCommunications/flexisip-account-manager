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
use App\PasswordAlgorithm;
use App\Space;
use Tests\TestCase;

class ApiSpaceTest extends TestCase
{
    protected $method = 'POST';
    protected $route = '/api/spaces';
    protected $accountRoute = '/api/accounts';

    public function testBaseAdmin()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $secondDomain = Space::factory()->secondDomain()->create();
        $username = 'foo';

        // Admin domain
        $this->keyAuthenticated($admin)
            ->json($this->method, $this->accountRoute, [
                'username' => $username,
                'domain' => $admin->domain,
                'password' => '123456',
            ])
            ->assertOk();

        // Second domain
        $this->keyAuthenticated($admin)
            ->json($this->method, $this->accountRoute, [
                'username' => $username,
                // The domain is ignored there, to fallback on the admin one
                'domain' => $secondDomain->domain,
                'password' => '123456',
            ])
            ->assertJsonValidationErrors(['username']);

        // Admin domain is now a super domain
        Space::where('domain', $admin->domain)->update(['super' => true]);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->accountRoute, [
                'username' => $username,
                'domain' => $secondDomain->domain,
                'password' => '123456',
            ])
            ->assertOk();
    }

    public function testSuperAdmin()
    {
        $admin = Account::factory()->superAdmin()->create();
        $admin->generateUserApiKey();

        $thirdDomain = 'third.domain';
        $accountRealm = 'account.realm';

        $response = $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'name' => $thirdDomain,
                'domain' => $thirdDomain,
                'host' => $thirdDomain,
            ])
            ->assertCreated()
            ->assertJsonFragment([
                'super' => false,
            ]);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'name' => 'Another Domain',
                'domain' => 'baddomain',
                'host' => $thirdDomain,
            ])
            ->assertJsonValidationErrors(['domain']);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'name' => 'Another Domain',
                'domain' => 'another.domain',
                'host' => 'another.host',
            ]);

        $this->keyAuthenticated($admin)
            ->json('GET', $this->route)
            ->assertJsonFragment([
                'name' => $thirdDomain,
                'domain' => $thirdDomain,
                'host' => $thirdDomain,
            ])
            ->assertOk();

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
                'name' => $thirdDomain,
                'domain' => $thirdDomain,
                'host' => $thirdDomain,
                'super' => true,
                'hide_settings' => true
            ])
            ->assertOk();

        $this->keyAuthenticated($admin)
            ->json('DELETE', $this->route . '/' . $thirdDomain)
            ->assertOk();

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
            ->assertOk();
    }

    public function testUserCreation()
    {
        $admin = Account::factory()->superAdmin()->create();
        $admin->generateUserApiKey();

        $domain = 'domain.com';

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->accountRoute, [
                'username' => 'first',
                'domain' => $domain,
                'password' => '123456',
            ])->assertStatus(403);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'name' => $domain,
                'domain' => $domain,
                'host' => $domain,
                'super' => false,
                'max_accounts' => 1
            ])->assertCreated();

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->accountRoute, [
                'username' => 'first',
                'domain' => $domain,
                'password' => '123456',
            ])->assertOk();

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->accountRoute, [
                'username' => 'second',
                'domain' => $domain,
                'password' => '123456',
            ])->assertStatus(403);
    }

    public function testHashAlgorithmChange()
    {
        $admin = Account::factory()->superAdmin()->create();
        $admin->generateUserApiKey();
        $password = fake()->password();

        $this->keyAuthenticated($admin)
            ->json($this->method, '/api/accounts/me/password', [
                'password' => $password,
            ])->assertJsonFragment([
                    'passwords' => [
                        ['algorithm' => PasswordAlgorithm::SHA256->value]
                    ]
                ]);

        // Switch to MD5

        $admin->space->digestAuthenticationConfiguration->default_password_algorithm = PasswordAlgorithm::MD5->value;
        $admin->space->digestAuthenticationConfiguration->save();

        $this->keyAuthenticated($admin)
            ->json($this->method, '/api/accounts/me/password', [
                'password' => fake()->password(),
                'old_password' => 'wrong'
            ])->assertJsonValidationErrorFor('old_password');

        $this->keyAuthenticated($admin)
            ->json($this->method, '/api/accounts/me/password', [
                'password' => fake()->password(),
                'old_password' => $password
            ])->assertJsonFragment([
                    'passwords' => [
                        ['algorithm' => PasswordAlgorithm::MD5->value]
                    ]
                ]);
    }
}
