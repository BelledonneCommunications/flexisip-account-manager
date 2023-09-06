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
use App\Account;
use App\AccountCreationToken;
use App\AccountTombstone;
use App\ActivationExpiration;
use App\Admin;
use App\Alias as AppAlias;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

class ApiAccountTest extends TestCase
{
    use RefreshDatabase;

    protected $route = '/api/accounts';
    protected $method = 'POST';

    public function testMandatoryFrom()
    {
        Password::factory()->create();
        $response = $this->json($this->method, $this->route);
        $response->assertStatus(422);
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
        $admin = Admin::factory()->create();
        $password = $admin->account->passwords()->first();
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

    public function testUsernameNotPhone()
    {
        $admin = Admin::factory()->create();
        $password = $admin->account->passwords()->first();
        $password->account->generateApiKey();
        $password->account->save();

        $username = '+33612121212';
        $domain = 'example.com';

        $response = $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ]);

        $response->assertJsonValidationErrors(['username']);
    }

    public function testUsernameNotSIP()
    {
        $admin = Admin::factory()->create();
        $password = $admin->account->passwords()->first();
        $password->account->generateApiKey();
        $password->account->save();

        $username = 'blabla🔥';
        $domain = 'example.com';

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
        $configDomain = 'sip.domain.com';
        config()->set('app.sip_domain', $configDomain);

        $admin = Admin::factory()->create();
        $password = $admin->account->passwords()->first();
        $username = 'foobar';
        $domain = 'example.com';

        config()->set('app.admins_manage_multi_domains', false);

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

        $this->assertFalse(empty($response1['confirmation_key']));
        $this->assertFalse(empty($response1['provisioning_token']));
    }

    public function testAdminMultiDomains()
    {
        $configDomain = 'sip.domain.com';
        config()->set('app.sip_domain', $configDomain);
        config()->set('app.admins_manage_multi_domains', true);

        $admin = Admin::factory()->create();
        $password = $admin->account->passwords()->first();
        $password->account->generateApiKey();
        $password->account->save();

        $username = 'foobar';
        $domain1 = 'example.com';
        $domain2 = 'foobar.com';

        $response0 = $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain1,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ]);

        $response0
            ->assertStatus(200)
            ->assertJson([
                'username' => $username,
                'domain' => $domain1
            ]);

        $response1 = $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route, [
                'username' => $username,
                'domain' => $domain2,
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ]);

        $response1
            ->assertStatus(200)
            ->assertJson([
                'username' => $username,
                'domain' => $domain2
            ]);

        $this->keyAuthenticated($password->account)
            ->get($this->route)
            ->assertStatus(200)
            ->assertJson(['data' => [
                [
                    'username' => $admin->account->username,
                    'domain' => $admin->account->domain
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

    public function testDomainInTestDeployment()
    {
        $configDomain = 'testdomain.com';
        config()->set('app.admins_manage_multi_domains', true);
        config()->set('app.sip_domain', 'anotherdomain.com');

        $admin = Admin::factory()->create();
        $password = $admin->account->passwords()->first();
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

        $this->assertFalse(empty($response1['confirmation_key']));
        $this->assertFalse(empty($response1['provisioning_token']));
    }

    public function testUsernameNoDomain()
    {
        $admin = Admin::factory()->create();
        $password = $admin->account->passwords()->first();

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
        $admin = Admin::factory()->create();
        $password = $admin->account->passwords()->first();

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
        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();
        $password = $admin->account->passwords()->first();

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
                'admin' => true, // Not a boolean but actually the admin JSON object
            ]);

        $this->assertTrue(!empty($response1['confirmation_key']));
        $this->assertFalse(empty($response1['provisioning_token']));
    }

    public function testActivated()
    {
        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();
        $password = $admin->account->passwords()->first();

        $username = 'username';

        $response0 = $this->generateFirstResponse($password);
        $response1 = $this->generateSecondResponse($password, $response0)
            ->json($this->method, $this->route, [
                'username' => $username,
                'algorithm' => 'SHA-256',
                'password' => 'blabla',
                'activated' => true,
            ]);

        $response1
            ->assertStatus(200)
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => config('app.sip_domain'),
                'activated' => true,
            ]);

        $this->assertTrue(empty($response1['confirmation_key']));
        $this->assertTrue(empty($response1['provisioning_token']));
    }

    public function testNotActivated()
    {
        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();
        $password = $admin->account->passwords()->first();

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

        $this->assertFalse(empty($response1['confirmation_key']));
        $this->assertFalse(empty($response1['provisioning_token']));
    }

    public function testSimpleAccount()
    {
        $password = Password::factory()->create();
        $password->account->activated = false;
        $password->account->generateApiKey();
        $password->account->save();

        $realm = 'realm.com';
        config()->set('app.realm', $realm);

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

    public function testActivateEmail()
    {
        $confirmationKey = '0123456789abc';
        $password = Password::factory()->create();
        $password->account->generateApiKey();
        $password->account->confirmation_key = $confirmationKey;
        $password->account->activated = false;
        $password->account->save();

        $expiration = new ActivationExpiration;
        $expiration->account_id = $password->account->id;
        $expiration->expires = Carbon::now()->subYear();
        $expiration->save();

        $this->get($this->route . '/' . $password->account->identifier . '/info')
            ->assertStatus(200)
            ->assertJson([
                'activated' => false
            ]);

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route . '/blabla/activate/email', [
                'confirmation_key' => $confirmationKey
            ])
            ->assertStatus(404);

        $activateEmailRoute = $this->route . '/' . $password->account->identifier . '/activate/email';

        $this->keyAuthenticated($password->account)
            ->json($this->method, $activateEmailRoute, [
                'confirmation_key' => $confirmationKey . 'longer'
            ])
            ->assertStatus(422);

        $this->keyAuthenticated($password->account)
            ->json($this->method, $activateEmailRoute, [
                'confirmation_key' => 'X123456789abc'
            ])
            ->assertStatus(404);

        // Expired
        $this->keyAuthenticated($password->account)
            ->json($this->method, $activateEmailRoute, [
                'confirmation_key' => $confirmationKey
            ])
            ->assertStatus(403);

        $expiration->delete();

        $this->keyAuthenticated($password->account)
            ->json($this->method, $activateEmailRoute, [
                'confirmation_key' => $confirmationKey
            ])
            ->assertStatus(200);

        $this->get($this->route . '/' . $password->account->identifier . '/info')
            ->assertStatus(200)
            ->assertJson([
                'activated' => true
            ]);
    }

    public function testUniqueEmailAdmin()
    {
        $email = 'collision@email.com';

        $existing = Password::factory()->create();
        $existing->account->activated = false;
        $existing->account->email = $email;
        $existing->account->save();

        config()->set('app.account_email_unique', true);

        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();
        $admin->account->save();

        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route, [
                'username' => 'hop',
                'email' => $email,
                'domain' => 'server.com',
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testNonAsciiPasswordAdmin()
    {
        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();
        $admin->account->save();

        $username = 'username';

        $response = $this->generateFirstResponse($admin->account->passwords()->first(), $this->method, $this->route);
        $this->generateSecondResponse($admin->account->passwords()->first(), $response)
            ->json($this->method, $this->route, [
                'username' => $username,
                'email' => 'email@test.com',
                'domain' => 'server.com',
                'algorithm' => 'SHA-256',
                'password' => 'nonascii€',
            ])
            ->assertStatus(200);

        $password = Account::where('username', $username)->first()->passwords()->first();

        $response = $this->generateFirstResponse($password, 'GET', '/api/accounts/me');
        $response = $this->generateSecondResponse($password, $response)
            ->json('GET', '/api/accounts/me');
    }

    public function testEditAdmin()
    {
        $password = Password::factory()->create();
        $account = $password->account;

        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();
        $admin->account->save();

        $username = 'changed';
        $algorithm = 'MD5';
        $password = 'other';

        $this->keyAuthenticated($admin->account)
            ->json('PUT', $this->route . '/1234')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['username']);

        $this->keyAuthenticated($admin->account)
            ->json('PUT', $this->route . '/1234', [
                'username' => 'good'
            ])
            ->assertStatus(422);

        $this->keyAuthenticated($admin->account)
            ->json('PUT', $this->route . '/' . $account->id, [
                'username' => $username,
                'algorithm' => $algorithm,
                'password' => $password,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'username' => $username
        ]);

        $this->assertDatabaseHas('passwords', [
            'account_id' => $account->id,
            'algorithm' => $algorithm
        ]);
    }

    /**
     * /!\ Dangerous endpoints
     */
    public function testRecover()
    {
        $confirmationKey = '0123';
        $password = Password::factory()->create();
        $password->account->generateApiKey();
        $password->account->confirmation_key = $confirmationKey;
        $password->account->activated = false;
        $password->account->save();

        config()->set('app.dangerous_endpoints', true);

        $this->assertDatabaseHas('accounts', [
            'username' => $password->account->username,
            'domain' => $password->account->domain,
            'activated' => false
        ]);

        $this->get($this->route . '/' . $password->account->identifier . '/recover/' . $confirmationKey)
            ->assertJson(['passwords' => [[
                'password' => $password->password,
                'algorithm' => $password->algorithm
            ]]])
            ->assertStatus(200);

        $this->json('GET', $this->route . '/' . $password->account->identifier . '/recover/' . $confirmationKey)
            ->assertStatus(404);

        $this->assertDatabaseHas('accounts', [
            'username' => $password->account->username,
            'domain' => $password->account->domain,
            'confirmation_key' => null,
            'activated' => true
        ]);

        // Recover by alias

        $newConfirmationKey = '1345';

        $password->account->confirmation_key = $newConfirmationKey;
        $password->account->save();

        $phone = '+1234';

        $alias = new AppAlias;
        $alias->alias = $phone;
        $alias->domain = $password->account->domain;
        $alias->account_id = $password->account->id;
        $alias->save();

        $this->get($this->route . '/' . $phone . '@' . $alias->domain . '/recover/' . $newConfirmationKey)
            ->assertJson(['passwords' => [[
                'password' => $password->password,
                'algorithm' => $password->algorithm
            ]]])
            ->assertStatus(200);
    }

    /**
     * /!\ Dangerous endpoints
     */
    public function testRecoverPhone()
    {
        $phone = '+3361234';

        $password = Password::factory()->create();
        $password->account->generateApiKey();
        $password->account->activated = false;
        $password->account->save();

        config()->set('app.dangerous_endpoints', true);

        $alias = new AppAlias;
        $alias->alias = $phone;
        $alias->domain = $password->account->domain;
        $alias->account_id = $password->account->id;
        $alias->save();

        $this->json($this->method, $this->route . '/recover-by-phone', [
            'phone' => $phone
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['account_creation_token']);

        $this->json($this->method, $this->route . '/recover-by-phone', [
            'phone' => $phone,
            'account_creation_token' => 'wrong'
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['account_creation_token']);
        $token = AccountCreationToken::factory()->create();

        $this->json($this->method, $this->route . '/recover-by-phone', [
            'phone' => $phone,
            'account_creation_token' => $token->token
        ])
            ->assertStatus(200);

        $password->account->refresh();

        // Use the token a second time
        $this->json($this->method, $this->route . '/recover-by-phone', [
            'phone' => $phone,
            'account_creation_token' => $token->token
        ])
            ->assertStatus(422);

        $this->get($this->route . '/' . $password->account->identifier . '/recover/' . $password->account->confirmation_key)
            ->assertStatus(200)
            ->assertJson([
                'activated' => true
            ]);

        $this->get($this->route . '/' . $phone . '/info-by-phone')
            ->assertStatus(200)
            ->assertJson([
                'activated' => true,
                'phone' => true
            ]);

        $this->get($this->route . '/+1234/info-by-phone')
            ->assertStatus(404);

        $this->json('GET', $this->route . '/' . $password->account->identifier . '/info-by-phone')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);

        // Check the mixed username/phone resolution...
        $password->account->username = $phone;
        $password->account->save();

        $alias->delete();

        $this->get($this->route . '/' . $phone . '/info-by-phone')
            ->assertStatus(200)
            ->assertJson([
                'activated' => true,
                'phone' => false
            ]);

        $this->assertDatabaseHas('account_creation_tokens', [
            'used' => true,
            'account_id' => $password->account->id,
        ]);
    }

    /**
     * /!\ Dangerous endpoints
     */

    public function testCreatePublic()
    {
        $username = 'publicuser';

        config()->set('app.dangerous_endpoints', true);

        // Missing email
        $this->json($this->method, $this->route . '/public', [
            'username' => $username,
            'algorithm' => 'SHA-256',
            'password' => '2',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->json($this->method, $this->route . '/public', [
            'username' => $username,
            'algorithm' => 'SHA-256',
            'password' => '2',
            'email' => 'john@doe.tld',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['account_creation_token']);

        $token = AccountCreationToken::factory()->create();
        $userAgent = 'User Agent Test';

        $this->withHeaders([
            'User-Agent' => $userAgent,
        ])->json($this->method, $this->route . '/public', [
            'username' => $username,
            'algorithm' => 'SHA-256',
            'password' => '2',
            'email' => 'john@doe.tld',
            'account_creation_token' => $token->token
        ])
            ->assertStatus(200)
            ->assertJson([
                'activated' => false
            ]);

        // Re-use the token
        $this->withHeaders([
            'User-Agent' => $userAgent,
        ])->json($this->method, $this->route . '/public', [
            'username' => $username . 'foo',
            'algorithm' => 'SHA-256',
            'password' => '2',
            'email' => 'john@doe.tld',
            'account_creation_token' => $token->token
        ])
            ->assertStatus(422);

        // Already created
        $this->json($this->method, $this->route . '/public', [
            'username' => $username,
            'algorithm' => 'SHA-256',
            'password' => '2',
            'email' => 'john@doe.tld',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['username']);

        // Email is now unique
        config()->set('app.account_email_unique', true);

        $this->json($this->method, $this->route . '/public', [
            'username' => 'johndoe',
            'algorithm' => 'SHA-256',
            'password' => '2',
            'email' => 'john@doe.tld',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseHas('accounts', [
            'username' => $username,
            'domain' => config('app.sip_domain'),
            'user_agent' => $userAgent
        ]);

        $this->assertDatabaseHas('account_creation_tokens', [
            'used' => true,
            'account_id' => Account::where('username', $username)->first()->id,
        ]);
    }

    public function testCreatePublicPhone()
    {
        $phone = '+12345';

        config()->set('app.dangerous_endpoints', true);

        // Bad phone format
        $this->json($this->method, $this->route . '/public', [
            'phone' => 'username',
            'algorithm' => 'SHA-256',
            'password' => '2',
            'email' => 'john@doe.tld',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);

        $token = AccountCreationToken::factory()->create();

        $this->json($this->method, $this->route . '/public', [
            'phone' => $phone,
            'algorithm' => 'SHA-256',
            'password' => '2',
            'email' => 'john@doe.tld',
            'account_creation_token' => $token->token
        ])
            ->assertStatus(200)
            ->assertJson([
                'activated' => false
            ]);

        // Already exists
        $this->json($this->method, $this->route . '/public', [
            'phone' => $phone,
            'algorithm' => 'SHA-256',
            'password' => '2',
            'email' => 'john@doe.tld',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);

        $this->assertDatabaseHas('accounts', [
            'username' => $phone,
            'domain' => config('app.sip_domain')
        ]);

        $this->assertDatabaseHas('aliases', [
            'alias' => $phone,
            'domain' => config('app.sip_domain')
        ]);
    }

    public function testActivatePhone()
    {
        $confirmationKey = '0123';
        $password = Password::factory()->create();
        $password->account->generateApiKey();
        $password->account->confirmation_key = $confirmationKey;
        $password->account->activated = false;
        $password->account->save();

        $expiration = new ActivationExpiration;
        $expiration->account_id = $password->account->id;
        $expiration->expires = Carbon::now()->subYear();
        $expiration->save();

        $this->get($this->route . '/' . $password->account->identifier . '/info')
            ->assertStatus(200)
            ->assertJson([
                'activated' => false
            ]);

        // Expired
        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route . '/' . $password->account->identifier . '/activate/phone', [
                'confirmation_key' => $confirmationKey
            ])
            ->assertStatus(403);

        $expiration->delete();

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route . '/' . $password->account->identifier . '/activate/phone', [
                'confirmation_key' => $confirmationKey
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('accounts', [
            'username' => $password->account->username,
            'domain' => $password->account->domain,
            'activated' => true
        ]);
    }

    public function testChangeEmail()
    {
        $password = Password::factory()->create();
        $otherAccount = Password::factory()->create();
        $password->account->generateApiKey();
        $newEmail = 'new_email@test.com';

        // Bad email
        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route . '/me/email/request', [
                'email' => 'gnap'
            ])
            ->assertStatus(422);

        // Same email
        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route . '/me/email/request', [
                'email' => $password->account->email
            ])
            ->assertStatus(422);

        // Correct email
        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route . '/me/email/request', [
                'email' => $newEmail
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($password->account)
            ->get($this->route . '/me')
            ->assertStatus(200)
            ->assertJson([
                'username' => $password->account->username,
                'email_change_code' => [
                    'email' => $newEmail
                ]
            ]);

        // Email already exists
        config()->set('app.account_email_unique', true);

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route . '/me/email/request', [
                'email' => $otherAccount->account->email
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testChangePassword()
    {
        $account = Account::factory()->create();
        $account->generateApiKey();
        $password = 'password';
        $algorithm = 'MD5';
        $newPassword = 'new_password';
        $newAlgorithm = 'SHA-256';

        // Wrong algorithm
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route . '/me/password', [
                'algorithm' => '123',
                'password' => $password
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['algorithm']);

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
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['old_password']);

        // Set the new password with incorrect old password
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route . '/me/password', [
                'algorithm' => $newAlgorithm,
                'old_password' => 'blabla',
                'password' => $newPassword
            ])
            ->assertJsonValidationErrors(['old_password'])
            ->assertStatus(422);

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
        $password = Password::factory()->create();

        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        // deactivate
        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/' . $password->account->id . '/deactivate')
            ->assertStatus(200)
            ->assertJson([
                'activated' => false
            ]);

        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/' . $password->account->id)
            ->assertStatus(200)
            ->assertJson([
                'activated' => false
            ]);

        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/' . $password->account->id . '/activate')
            ->assertStatus(200)
            ->assertJson([
                'activated' => true
            ]);

        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/' . $password->account->id)
            ->assertStatus(200)
            ->assertJson([
                'activated' => true
            ]);

        // Search feature
        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/' . $password->account->identifier . '/search')
            ->assertStatus(200)
            ->assertJson([
                'id' => $password->account->id,
                'activated' => true
            ]);

        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/' . $password->account->email . '/search-by-email')
            ->assertStatus(200)
            ->assertJson([
                'id' => $password->account->id,
                'activated' => true
            ]);

        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/wrong@email.com/search-by-email')
            ->assertStatus(404);
    }

    public function testRecoverByEmail()
    {
        $email = 'collision@email.com';

        $account = Password::factory()->create();
        $account->account->email = $email;
        $account->account->save();

        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();
        $admin->account->save();

        $response = $this->keyAuthenticated($admin->account)
            ->post($this->route . '/' . $account->id . '/recover-by-email')
            ->assertStatus(200);

        $this->assertNotEquals($response->json('confirmation_key'), $account->confirmation_key);
        $this->assertNotEquals($response->json('provisioning_token'), $account->provisioning_token);
    }

    public function testGetAll()
    {
        Password::factory()->create();

        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        // /accounts
        $this->keyAuthenticated($admin->account)
            ->get($this->route)
            ->assertStatus(200)
            ->assertJson([
                'total' => 2
            ]);

        // /accounts/id
        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/' . $admin->id)
            ->assertStatus(200)
            ->assertJson([
                'id' => 1,
                'phone' => null
            ]);
    }

    public function testCodeExpires()
    {
        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        // Activated, no no confirmation_key
        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route, [
                'username' => 'foobar',
                'algorithm' => 'SHA-256',
                'password' => '123456',
                'activated' => true,
                'confirmation_key_expires' => '2040-12-12 12:12:12'
            ])
            ->assertStatus(200)
            ->assertJson([
                'confirmation_key_expires' => null
            ]);

        // Bad datetime format
        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route, [
                'username' => 'foobar2',
                'algorithm' => 'SHA-256',
                'password' => '123456',
                'activated' => false,
                'confirmation_key_expires' => 'abc'
            ])
            ->assertStatus(422);

        // Bad datetime format
        $this->keyAuthenticated($admin->account)
            ->json($this->method, $this->route, [
                'username' => 'foobar2',
                'algorithm' => 'SHA-256',
                'password' => '123456',
                'activated' => false,
                'confirmation_key_expires' => '2040-12-12 12:12:12'
            ])
            ->assertStatus(200)
            ->assertJson([
                'confirmation_key_expires' => '2040-12-12 12:12:12'
            ]);;
    }

    public function testDelete()
    {
        $password = Password::factory()->create();

        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $this->keyAuthenticated($admin->account)
            ->delete($this->route . '/' . $password->account->id)
            ->assertStatus(200);

        $this->assertEquals(1, AccountTombstone::count());

        $this->keyAuthenticated($admin->account)
            ->get($this->route . '/' . $password->account->id)
            ->assertStatus(404);
    }
}
