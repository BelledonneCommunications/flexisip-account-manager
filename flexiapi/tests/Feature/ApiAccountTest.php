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
use App\AccountTombstone;
use App\Password;
use App\Space;
use Carbon\Carbon;
use Tests\TestCase;

class ApiAccountTest extends TestCase
{
    protected $route = '/api/accounts';
    protected $method = 'POST';

    public function testMandatoryFrom()
    {
        Password::factory()->create();
        $response = $this->json($this->method, $this->route);
        $response->assertStatus(401);
    }

    public function testNotAdminForbidden()
    {
        $password = Password::factory()->create();
        $response0 = $this->generateFirstResponse($password);
        $response1 = $this->generateSecondResponse($password, $response0)
            ->json($this->method, $this->route);

        $response1->assertStatus(403);
    }

    public function testAdminOk()
    {
        $password = Password::factory()->admin()->create();
        $username = 'foobar';

        $response0 = $this->generateFirstResponse($password);
        $response1 = $this->generateSecondResponse($password, $response0)
            ->json($this->method, $this->route, [
                'username' => $username,
                'algorithm' => 'SHA-256',
                'password' => '123456',
                'dtmf_protocol' => 'sipinfo'
            ]);

        $response1
            ->assertStatus(200)
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'activated' => false,
                'dtmf_protocol' => 'sipinfo'
            ]);
    }

    public function testEmptyDevices()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();

        $this->keyAuthenticated($account)
            ->get($this->route . '/me/devices')
            ->assertStatus(200)
            ->assertSee('{}');
    }

    public function testUsernameNotPhone()
    {
        $account = Account::factory()->admin()->create();
        $account->generateUserApiKey();

        $username = '+33612121212';
        $domain = Space::first()->domain;

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertJsonValidationErrors(['username']);

        config()->set('app.allow_phone_number_username_admin_api', true);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertStatus(200);
    }

    public function testUsernameNotSIP()
    {
        $password = Password::factory()->admin()->create();
        $password->account->generateUserApiKey();

        $username = 'blabla🔥';
        $domain = Space::first()->domain;

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])->assertJsonValidationErrors(['username']);

        // Change the regex
        config()->set('app.account_username_regex', '^[a-z0-9🔥+_.-]*$');

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])->assertStatus(200);

        $username = 'blabla hop';
        $domain = 'example.com';

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])->assertJsonValidationErrors(['username']);
    }

    public function testDomain()
    {
        $configDomain = 'sip2.example.com';

        Space::factory()->domain($configDomain)->create();
        config()->set('app.sip_domain', $configDomain);

        $password = Password::factory()->admin()->create();
        $username = 'foobar';
        $domain = Space::first()->domain;

        $response0 = $this->generateFirstResponse($password);
        $response1 = $this->generateSecondResponse($password, $response0)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ]);

        $response1
            ->assertStatus(200)
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => $configDomain,
                'activated' => false
            ]);

        $this->assertFalse(empty($response1['provisioning_token']));
    }

    public function testAdminMultiDomains()
    {
        $configDomain = 'sip2.example.com';
        config()->set('app.sip_domain', $configDomain);

        $account = Account::factory()->superAdmin()->create();
        $account->generateUserApiKey();
        $account->save();

        $username = 'foobar';
        $domain1 = Space::first()->domain;
        $domain2 = Space::factory()->secondDomain()->create()->domain;

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain1,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertStatus(200)
            ->assertJson([
                'username' => $username,
                'domain' => $domain1
            ]);

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain2,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertStatus(200)
            ->assertJson([
                'username' => $username,
                'domain' => $domain2
            ]);

        $this->keyAuthenticated($account)
            ->get($this->route)
            ->assertStatus(200)
            ->assertJson(['data' => [
                [
                    'username' => $account->username,
                    'domain' => $account->domain
                ],
                [
                    'username' => $username,
                    'domain' => $domain1
                ],
                [
                    'username' => $username,
                    'domain' => $domain2
                ]
            ]]);
    }

    public function testCreateDomainAsAdmin()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();
        $admin->save();

        $username = 'foo';
        $newDomain = 'new.domain';

        // Standard admin
        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $newDomain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertStatus(422);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $admin->domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertStatus(200);
    }

    /*public function testCreateDomainAsSuperAdmin()
    {
        $superAdmin = Account::factory()->superAdmin()->create();
        $superAdmin->generateUserApiKey();
        $superAdmin->save();

        $username = 'foo';
        $newDomain = 'new.domain';

        // Super admin
        $this->keyAuthenticated($superAdmin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $newDomain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertStatus(200)
            ->assertJson([
                'username' => $username,
                'domain' => $newDomain
            ]);

        $this->assertDatabaseHas('spaces', [
            'domain' => $newDomain
        ]);
    }*/

    public function testDomainInTestDeployment()
    {
        $configDomain = 'sip2.example.com';
        Space::factory()->domain($configDomain)->create();
        config()->set('app.sip_domain', $configDomain);

        $password = Password::factory()->admin()->create();
        $username = 'foobar';

        $response0 = $this->generateFirstResponse($password);
        $response1 = $this->generateSecondResponse($password, $response0)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $configDomain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ]);

        $response1
            ->assertStatus(200)
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => $configDomain,
                'activated' => false
            ]);

        $this->assertFalse(empty($response1['provisioning_token']));
    }

    public function testUsernameNoDomain()
    {
        $password = Password::factory()->admin()->create();

        $username = 'username';

        $response0 = $this->generateFirstResponse($password);
        $response1 = $this->generateSecondResponse($password, $response0)
            ->json($this->method, $this->route, [
                'username' => $username,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ]);

        $response1
            ->assertStatus(200)
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => config('app.sip_domain'),
                'activated' => false,
            ]);
    }

    public function testUsernameEmpty()
    {
        $password = Password::factory()->admin()->create();

        $response0 = $this->generateFirstResponse($password);
        $response1 = $this->generateSecondResponse($password, $response0)
            ->json($this->method, $this->route, [
                'username' => '',
                'algorithm' => 'SHA-256',
                'password' => '2',
            ]);

        $response1->assertStatus(422);
    }

    public function testAdmin()
    {
        $password = Password::factory()->admin()->create();

        $username = 'username';

        $response0 = $this->generateFirstResponse($password);
        $response1 = $this->generateSecondResponse($password, $response0)
            ->json($this->method, $this->route, [
                'username' => $username,
                'algorithm' => 'SHA-256',
                'password' => 'blabla',
                'admin' => true,
            ]);

        $response1
            ->assertStatus(200)
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => config('app.sip_domain'),
                'admin' => true,
            ]);

        $this->assertFalse(empty($response1['provisioning_token']));
    }

    public function testAdminWithDictionary()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $entryKey = 'foo';
        $entryValue = 'bar';
        $entryNewKey = 'new_key';
        $entryNewValue = 'new_value';
        $domain = Space::first()->domain;

        $result = $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => 'john',
                'domain' => $domain,
                'password' => 'password123',
                'algorithm' => 'SHA-256',
                'dictionary' => [
                    $entryKey => $entryValue
                ]
            ])
            ->assertStatus(200)
            ->assertJson([
                'dictionary' => [
                    $entryKey => $entryValue
                ]
            ]);

        $accountId = $result->json('id');

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => 'john2',
                'domain' => $domain,
                'password' => 'password123',
                'algorithm' => 'SHA-256',
                'dictionary' => [
                    $entryKey => ['hey' => 'hop']
                ]
            ])->assertJsonValidationErrors(['dictionary']);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => 'john2',
                'domain' => $domain,
                'password' => 'password123',
                'algorithm' => 'SHA-256',
                'dictionary' => 'hop'
        ])->assertJsonValidationErrors(['dictionary']);

        // Account update

        $this->keyAuthenticated($admin)
            ->json('PUT', $this->route . '/' . $accountId, [
                'username' => 'john3',
                'password' => 'bar',
                'algorithm' => 'SHA-256',
                'dictionary' => [
                    $entryNewKey => $entryNewValue
                ]
            ])
            ->assertJsonMissing([
                'dictionary' => [
                    $entryKey => $entryValue
                ]
            ])
            ->assertJson([
                'dictionary' => [
                    $entryNewKey => $entryNewValue
                ]
            ])
            ->assertStatus(200);

        // ...twice

        $this->keyAuthenticated($admin)
            ->json('PUT', $this->route . '/' . $accountId, [
                'username' => 'john3',
                'password' => 'bar',
                'algorithm' => 'SHA-256',
                'dictionary' => [
                    $entryNewKey => $entryNewValue
                ]
            ])
            ->assertJsonMissing([
                'dictionary' => [
                    $entryKey => $entryValue
                ]
            ])
            ->assertJson([
                'dictionary' => [
                    $entryNewKey => $entryNewValue
                ]
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('GET', $this->route . '/' . $accountId)
            ->assertStatus(200)
            ->assertJsonMissing([
                'dictionary' => [
                    $entryKey => $entryValue
                ]
            ])
            ->assertJson([
                'dictionary' => [
                    $entryNewKey => $entryNewValue
                ]
            ]);

        // Clear

        $this->keyAuthenticated($admin)
            ->json('PUT', $this->route . '/' . $accountId, [
                'username' => 'john3',
                'password' => 'bar',
                'algorithm' => 'SHA-256',
                'dictionary' => []
            ])
            ->assertJson(['dictionary' => null])
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('GET', $this->route . '/' . $accountId)
            ->assertSee(['"dictionary":{}'], false)
            ->assertStatus(200);
    }

    public function testActivated()
    {
        $password = Password::factory()->admin()->create();
        $username = 'username';

        $response0 = $this->generateFirstResponse($password);
        $this->generateSecondResponse($password, $response0)
            ->json($this->method, $this->route, [
                'username' => $username,
                'algorithm' => 'SHA-256',
                'password' => 'blabla',
                'activated' => true,
            ])
            ->assertStatus(200)
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => config('app.sip_domain'),
                'activated' => true,
            ]);
    }

    public function testNotActivated()
    {
        $password = Password::factory()->admin()->create();

        $username = 'username';

        $response0 = $this->generateFirstResponse($password);
        $response1 = $this->generateSecondResponse($password, $response0)
            ->json($this->method, $this->route, [
                'username' => $username,
                'algorithm' => 'SHA-256',
                'password' => 'blabla',
                'activated' => false,
            ]);

        $response1->assertStatus(200)
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => config('app.sip_domain'),
                'activated' => false,
            ]);

        $this->assertFalse(empty($response1['provisioning_token']));
    }

    public function testSimpleAccount()
    {
        $realm = 'realm.com';

        Space::factory()->withRealm($realm)->create();

        $password = Password::factory()->create();
        $password->account->activated = false;
        $password->account->generateUserApiKey();
        $password->account->save();

        /**
         * Public information
         */
        $this->get($this->route . '/' . $password->account->identifier . '/info')
            ->assertStatus(200)
            ->assertJson([
                'activated' => false,
                'realm' => $realm
            ]);

        $password->account->activated = true;
        $password->account->save();

        /**
         * Retrieve the authenticated account
         */
        $this->keyAuthenticated($password->account)
            ->get($this->route . '/me')
            ->assertStatus(200)
            ->assertJson([
                'username' => $password->account->username,
                'activated' => true,
                'realm' => $realm
            ]);

        /**
         * Retrieve the authenticated account
         */
        $this->keyAuthenticated($password->account)
            ->delete($this->route . '/me')
            ->assertStatus(200);

        /**
         * Check again
         */
        $this->get($this->route . '/' . $password->account->identifier . '/info')
            ->assertStatus(404);
    }

    public function testUniqueEmailAdmin()
    {
        $email = 'collision@email.com';

        $existing = Password::factory()->create();
        $existing->account->activated = false;
        $existing->account->email = $email;
        $existing->account->save();

        config()->set('app.account_email_unique', true);

        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();
        $admin->save();

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => 'hop',
                'email' => $email,
                'domain' => 'server.com',
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])->assertJsonValidationErrors(['email']);
    }

    public function testNonAsciiPasswordAdmin()
    {
        $password = Password::factory()->admin()->create();
        $password->account->generateUserApiKey();

        $username = 'username';
        $domain = Space::first()->domain;

        $response = $this->generateFirstResponse($password, $this->method, $this->route);
        $this->generateSecondResponse($password, $response)
            ->json($this->method, $this->route, [
                'username' => $username,
                'email' => 'email@test.com',
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => 'nonascii€',
            ])
            ->assertStatus(200);

        $response = $this->generateFirstResponse($password, 'GET', '/api/accounts/me');
        $response = $this->generateSecondResponse($password, $response)
            ->json('GET', '/api/accounts/me');
    }

    public function testSendProvisioningEmail()
    {
        $password = Password::factory()->create();
        $account = $password->account;

        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();
        $admin->save();

        $this->keyAuthenticated($admin)
            ->json('POST', $this->route . '/' . $account->id . '/send_provisioning_email')
            ->assertStatus(403);

        $account->email = 'test@email.com';
        $account->save();

        $this->keyAuthenticated($admin)
            ->json('POST', $this->route . '/' . $account->id . '/send_provisioning_email')
            ->assertStatus(200);
    }

    public function testSendResetPasswordEmail()
    {
        $password = Password::factory()->create();
        $account = $password->account;

        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();
        $admin->save();

        $this->keyAuthenticated($admin)
            ->json('POST', $this->route . '/' . $account->id . '/send_reset_password_email')
            ->assertStatus(403);

        $account->email = 'test@email.com';
        $account->save();

        $this->keyAuthenticated($admin)
            ->json('POST', $this->route . '/' . $account->id . '/send_reset_password_email')
            ->assertStatus(200);
    }


    public function testEditAdmin()
    {
        $password = Password::factory()->create();
        $account = $password->account;

        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();
        $admin->save();

        $username = 'changed';
        $algorithm = 'MD5';
        $password = 'other';
        $newDisplayName = 'new_display_name';

        $this->keyAuthenticated($admin)
            ->json('PUT', $this->route . '/1234')
            ->assertJsonValidationErrors(['username']);

        $this->keyAuthenticated($admin)
            ->json('PUT', $this->route . '/1234', [
                'username' => 'good'
            ])
            ->assertStatus(422);

        $this->keyAuthenticated($admin)
            ->json('PUT', $this->route . '/' . $account->id, [
                'username' => $username,
                'algorithm' => $algorithm,
                'password' => $password,
                'display_name' => $newDisplayName
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('PUT', $this->route . '/' . $account->id, [
                'username' => $username,
                'algorithm' => $algorithm,
                'password' => $password,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'username' => $username,
            'display_name' => null
        ]);

        $this->assertDatabaseHas('passwords', [
            'account_id' => $account->id,
            'algorithm' => $algorithm
        ]);
    }

    public function testChangePassword()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();
        $password = 'password';
        $algorithm = 'MD5';
        $newPassword = 'new_password';
        $newAlgorithm = 'SHA-256';

        // Wrong algorithm
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route . '/me/password', [
                'algorithm' => '123',
                'password' => $password
            ])->assertJsonValidationErrors(['algorithm']);

        // Fresh password without an old one
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route . '/me/password', [
                'algorithm' => $algorithm,
                'password' => $password
            ])
            ->assertStatus(200);

        // First check
        $this->keyAuthenticated($account)
            ->get($this->route . '/me')
            ->assertStatus(200)
            ->assertJson([
                'username' => $account->username,
                'passwords' => [[
                    'algorithm' => $algorithm
                ]]
            ]);

        // Set new password without old one
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route . '/me/password', [
                'algorithm' => $newAlgorithm,
                'password' => $newPassword
            ])->assertJsonValidationErrors(['old_password']);

        // Set the new password with incorrect old password
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route . '/me/password', [
                'algorithm' => $newAlgorithm,
                'old_password' => 'blabla',
                'password' => $newPassword
            ])->assertJsonValidationErrors(['old_password']);

        // Set the new password
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route . '/me/password', [
                'algorithm' => $newAlgorithm,
                'old_password' => $password,
                'password' => $newPassword
            ])
            ->assertStatus(200);

        // Second check
        $this->keyAuthenticated($account)
            ->get($this->route . '/me')
            ->assertStatus(200)
            ->assertJson([
                'username' => $account->username,
                'passwords' => [[
                    'algorithm' => $newAlgorithm
                ]]
            ]);
    }

    public function testActivateDeactivate()
    {
        $account = Account::factory()->withEmail()->create();

        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        // deactivate
        $this->keyAuthenticated($admin)
            ->post($this->route . '/' . $account->id . '/deactivate')
            ->assertStatus(200)
            ->assertJson([
                'activated' => false
            ]);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $account->id)
            ->assertStatus(200)
            ->assertJson([
                'activated' => false
            ]);

        $this->keyAuthenticated($admin)
            ->post($this->route . '/' . $account->id . '/activate')
            ->assertStatus(200)
            ->assertJson([
                'activated' => true
            ]);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $account->id)
            ->assertStatus(200)
            ->assertJson([
                'activated' => true
            ]);

        // Search feature
        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $account->identifier . '/search')
            ->assertStatus(200)
            ->assertJson([
                'id' => $account->id,
                'activated' => true
            ]);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/wrong/search')
            ->assertStatus(404);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $account->email . '/search-by-email')
            ->assertStatus(200)
            ->assertJson([
                'id' => $account->id,
                'activated' => true
            ]);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/wrong@email.com/search-by-email')
            ->assertStatus(404);
    }

    public function testGetAll()
    {
        Password::factory()->create();

        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        // /accounts
        $this->keyAuthenticated($admin)
            ->get($this->route)
            ->assertStatus(200)
            ->assertJson([
                'total' => 2
            ]);

        // /accounts/id
        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $admin->id)
            ->assertStatus(200)
            ->assertJson([
                'id' => 2,
                'phone' => null
            ]);
    }

    public function testDelete()
    {
        $password = Password::factory()->create();

        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $this->keyAuthenticated($admin)
            ->delete($this->route . '/' . $password->account->id)
            ->assertStatus(200);

        $this->assertEquals(1, AccountTombstone::count());

        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $password->account->id)
            ->assertStatus(404);
    }
}
