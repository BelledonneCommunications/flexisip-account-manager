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
use Tests\TestCase;

class ApiAccountTest extends TestCase
{
    protected $route = '/api/accounts';
    protected $method = 'POST';

    public function testMandatoryFrom()
    {
        Password::factory()->create();
        $this->json('GET', '/api/accounts/me/api_key')
            ->assertStatus(401);
    }

    public function testNotAdminForbidden()
    {
        $account = Account::factory()->create();
        $account->generateUserApiKey();

        $this->keyAuthenticated($account)
            ->json($this->method, $this->route)
            ->assertForbidden();
    }

    public function testAdminOk()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();
        $username = 'foobar';

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'algorithm' => 'SHA-256',
                'password' => '123456',
                'dtmf_protocol' => 'sipinfo'
            ])
            ->assertOk()
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
            ->assertOk()
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
            ->assertOk();
    }

    public function testUsernameNotSIP()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $username = 'blabla🔥';
        $domain = Space::first()->domain;

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])->assertJsonValidationErrors(['username']);

        // Change the regex
        config()->set('app.account_username_regex', '^[a-z0-9🔥+_.-]*$');

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain,
                'sip_uri' => 'sip:' . $username . '@' . $domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])->assertOk();

        $username = 'blabla hop';
        $domain = 'example.com';

        $this->keyAuthenticated($admin)
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

        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();
        $username = 'foobar';
        $domain = Space::first()->domain;

        $response = $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertOk()
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => $configDomain,
                'activated' => false
            ]);

        $this->assertFalse(empty($response['provisioning_token']));
    }

    public function testAdminMultiDomains()
    {
        $username = 'foobar';
        $space1 = Space::factory()->create();
        $space2 = Space::factory()->secondDomain()->create();

        $superAdmin = Account::factory()->fromSpace($space1)->superAdmin()->create();
        $superAdmin->generateUserApiKey();
        $superAdmin->save();

        $space1Accounts = $this->setSpaceOnRoute($space1, route('accounts.index'));
        $space2Accounts = $this->setSpaceOnRoute($space2, route('accounts.index'));

        $this->keyAuthenticated($superAdmin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $space1->domain,
                'admin' => true,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertOk()
            ->assertJson([
                'username' => $username,
                'domain' => $space1->domain
            ]);

        $this->keyAuthenticated($superAdmin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $space2->domain,
                'admin' => true,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertOk()
            ->assertJson([
                'username' => $username,
                'domain' => $space2->domain
            ]);

        config()->set('app.sip_domain', null);

        $this->keyAuthenticated($superAdmin)
            ->get($space1Accounts)
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'username' => $superAdmin->username,
                        'domain' => $superAdmin->domain
                    ],
                    [
                        'username' => $username,
                        'domain' => $space1->domain
                    ]
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    [
                        'username' => $username,
                        'domain' => $space1->domain
                    ]
                ]
            ]);

        // Super admin on space 1
        $admin1 = Account::where('username', $username)
            ->where('domain', $space1->domain)
            ->first();
        $admin1->generateUserApiKey();

        $this->keyAuthenticated($admin1)
            ->get($space1Accounts)
            ->assertOk();

        $this->keyAuthenticated($admin1)
            ->get($space2Accounts)
            ->assertOk();

        $this->keyAuthenticated($superAdmin)
            ->get($space2Accounts)
            ->assertOk()
            ->assertJsonMissing([
                'data' => [
                    [
                        'username' => $superAdmin->username,
                        'domain' => $superAdmin->domain
                    ],
                    [
                        'username' => $username,
                        'domain' => $space1->domain
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    [
                        'username' => $username,
                        'domain' => $space2->domain
                    ]
                ]
            ]);

        // Simple admin on space 2
        $admin2 = Account::where('username', $username)
            ->where('domain', $space2->domain)
            ->first();
        $admin2->generateUserApiKey();

        $this->keyAuthenticated($admin2)
            ->get($space1Accounts)
            ->assertStatus(403);

        $this->keyAuthenticated($admin2)
            ->get($space2Accounts)
            ->assertOk();
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
            ->assertOk();
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
            ->assertOk()
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

        $username = 'foobar';

        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $response = $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $configDomain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertOk()
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => $configDomain,
                'activated' => false
            ]);

        $this->assertFalse(empty($response['provisioning_token']));
    }

    public function testUsernameNoDomain()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $username = 'username';

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertOk()
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => config('app.sip_domain'),
                'activated' => false,
            ]);
    }

    public function testUsernameEmpty()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => '',
                'algorithm' => 'SHA-256',
                'password' => '2',
            ])
            ->assertJsonValidationErrors(['username']);
    }

    public function testAdmin()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();
        $username = 'username';

        $response = $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'algorithm' => 'SHA-256',
                'password' => 'blabla',
                'admin' => true,
            ])
            ->assertOk()
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => config('app.sip_domain'),
                'admin' => true,
            ])
            ->assertJsonMissingPath('space');

        $this->assertFalse(empty($response['provisioning_token']));
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
            ->assertOk()
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
            ->assertOk();

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
            ->assertOk();

        $this->keyAuthenticated($admin)
            ->json('GET', $this->route . '/' . $accountId)
            ->assertOk()
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
            ->assertOk();

        $this->keyAuthenticated($admin)
            ->json('GET', $this->route . '/' . $accountId)
            ->assertSee(['"dictionary":{}'], false)
            ->assertOk();
    }

    public function testActivated()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();
        $username = 'username';

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'algorithm' => 'SHA-256',
                'password' => 'blabla',
                'activated' => true,
            ])
            ->assertOk()
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => config('app.sip_domain'),
                'activated' => true,
            ]);
    }

    public function testNotActivated()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();
        $username = 'username';

        $response = $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'algorithm' => 'SHA-256',
                'password' => 'blabla',
                'activated' => false,
            ])->assertOk()
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => config('app.sip_domain'),
                'activated' => false,
            ]);

        $this->assertFalse(empty($response['provisioning_token']));
    }

    public function testSimpleAccount()
    {
        $realm = 'realm.com';

        Space::factory()->withRealm($realm)->create();

        $password = Password::factory()->create();
        $password->account->generateUserApiKey();
        $password->account->activated = false;
        $password->account->save();

        $this->keyAuthenticated($password->account)
            ->get($this->route . '/me')
            ->assertOk()
            ->assertJson([
                'username' => $password->account->username,
                'activated' => false,
                'realm' => $realm
            ]);

        $password->account->activated = true;
        $password->account->save();

        $this->keyAuthenticated($password->account)
            ->get($this->route . '/me')
            ->assertOk()
            ->assertJson([
                'username' => $password->account->username,
                'activated' => true,
                'realm' => $realm
            ]);

        $this->keyAuthenticated($password->account)
            ->delete($this->route . '/me')
            ->assertOk();

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

        Space::where('domain', $existing->account->domain)->update(['unique_email' => true]);

        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();
        $admin->save();

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => 'hop',
                'email' => $email,
                'domain' => $existing->account->domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])->assertJsonValidationErrors(['email']);
    }

    public function testNonAsciiPasswordAdmin()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $username = 'username';
        $domain = Space::first()->domain;

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->route, [
                'username' => $username,
                'email' => 'email@test.com',
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => 'nonascii€',
            ])
            ->assertOk();
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
            ->assertOk();
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
            ->assertOk();
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
            ->assertOk();

        $this->keyAuthenticated($admin)
            ->json('PUT', $this->route . '/' . $account->id, [
                'username' => $username,
                'algorithm' => $algorithm,
                'password' => $password,
            ])
            ->assertOk();

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
            ->assertOk();

        // First check
        $this->keyAuthenticated($account)
            ->get($this->route . '/me')
            ->assertOk()
            ->assertJson([
                'username' => $account->username,
                'passwords' => [
                    [
                        'algorithm' => $algorithm
                    ]
                ]
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
            ->assertOk();

        // Second check
        $this->keyAuthenticated($account)
            ->get($this->route . '/me')
            ->assertOk()
            ->assertJson([
                'username' => $account->username,
                'passwords' => [
                    [
                        'algorithm' => $newAlgorithm
                    ]
                ]
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
            ->assertOk()
            ->assertJson([
                'activated' => false
            ]);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $account->id)
            ->assertOk()
            ->assertJson([
                'activated' => false
            ]);

        $this->keyAuthenticated($admin)
            ->post($this->route . '/' . $account->id . '/activate')
            ->assertOk()
            ->assertJson([
                'activated' => true
            ]);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $account->id)
            ->assertOk()
            ->assertJson([
                'activated' => true
            ]);

        // Search feature
        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $account->identifier . '/search')
            ->assertOk()
            ->assertJson([
                'id' => $account->id,
                'activated' => true
            ]);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/wrong/search')
            ->assertStatus(404);

        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $account->email . '/search-by-email')
            ->assertOk()
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
            ->assertOk()
            ->assertJson([
                'total' => 2
            ]);

        // /accounts/id
        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $admin->id)
            ->assertOk()
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
            ->assertOk();

        $this->assertEquals(1, AccountTombstone::count());

        $this->keyAuthenticated($admin)
            ->get($this->route . '/' . $password->account->id)
            ->assertStatus(404);
    }
}
