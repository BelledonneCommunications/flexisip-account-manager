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
use App\Admin;
use App\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class AccountApiTest extends TestCase
{
    use RefreshDatabase;

    protected $route = '/api/accounts';
    protected $method = 'POST';

    public function testMandatoryFrom()
    {
        $password = Password::factory()->create();
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

        config()->set('app.everyone_is_admin', true);

        $password = Password::factory()->create();
        $response0 = $this->generateFirstResponse($password);
        $response1 = $this->generateSecondResponse($password, $response0)
                          ->json($this->method, $this->route);

        $response1->assertStatus(422);
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
            ]);

        $response1
            ->assertStatus(200)
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'activated' => false,
            ]);
    }

    public function testDomain()
    {
        $admin = Admin::factory()->create();
        $password = $admin->account->passwords()->first();
        $username = 'foobar';
        $domain = 'example.com';

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
                'domain' => $domain,
                'activated' => false
            ]);

        $this->assertFalse(empty($response1['confirmation_key']));
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
                'password' => '2',
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
                'password' => '2',
                'activated' => false,
            ]);

        $response1
            ->assertStatus(200)
            ->assertJson([
                'id' => 2,
                'username' => $username,
                'domain' => config('app.sip_domain'),
                'activated' => false,
            ]);

        $this->assertFalse(empty($response1['confirmation_key']));
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
        $this->get($this->route.'/'.$password->account->identifier.'/info')
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
            ->get($this->route.'/me')
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
            ->delete($this->route.'/me')
            ->assertStatus(200);

        /**
         * Check again
         */
        $this->get($this->route.'/'.$password->account->identifier.'/info')
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

        $this->get($this->route.'/'.$password->account->identifier.'/info')
            ->assertStatus(200)
            ->assertJson([
                'activated' => false
            ]);

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route.'/blabla/activate/email', [
                'code' => $confirmationKey
            ])
            ->assertStatus(404);

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route.'/'.$password->account->identifier.'/activate/email', [
                'code' => $confirmationKey.'longer'
            ])
            ->assertStatus(422);

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route.'/'.$password->account->identifier.'/activate/email', [
                'code' => 'X123456789abc'
            ])
            ->assertStatus(404);

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route.'/'.$password->account->identifier.'/activate/email', [
                'code' => $confirmationKey
            ])
            ->assertStatus(200);

        $this->get($this->route.'/'.$password->account->identifier.'/info')
            ->assertStatus(200)
            ->assertJson([
                'activated' => true
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

        $this->get($this->route.'/'.$password->account->identifier.'/info')
            ->assertStatus(200)
            ->assertJson([
                'activated' => false
            ]);

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route.'/'.$password->account->identifier.'/activate/phone', [
                'code' => $confirmationKey
            ])
            ->assertStatus(200);

        $this->get($this->route.'/'.$password->account->identifier.'/info')
            ->assertStatus(200)
            ->assertJson([
                'activated' => true
            ]);
    }

    public function testChangeEmail()
    {
        $password = Password::factory()->create();
        $password->account->generateApiKey();
        $newEmail = 'new_email@test.com';

        // Bad email
        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route.'/me/email/request', [
                'email' => 'gnap'
            ])
            ->assertStatus(422);

        // Same email
        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route.'/me/email/request', [
                'email' => $password->account->email
            ])
            ->assertStatus(422);

        // Correct email
        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route.'/me/email/request', [
                'email' => $newEmail
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($password->account)
            ->get($this->route.'/me')
            ->assertStatus(200)
            ->assertJson([
                'username' => $password->account->username,
                'email_changed' => [
                    'new_email' => $newEmail
                ]
            ]);
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
            ->json($this->method, $this->route.'/me/password', [
                'algorithm' => '123',
                'password' => $password
            ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => ['algorithm' => true]
            ]);

        // Fresh password without an old one
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/me/password', [
                'algorithm' => $algorithm,
                'password' => $password
            ])
            ->assertStatus(200);

        // First check
        $this->keyAuthenticated($account)
            ->get($this->route.'/me')
            ->assertStatus(200)
            ->assertJson([
                'username' => $account->username,
                'passwords' => [[
                    'algorithm' => $algorithm
                ]]
            ]);

        // Set new password without old one
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/me/password', [
                'algorithm' => $newAlgorithm,
                'password' => $newPassword
            ])
            ->assertStatus(422)
            ->assertJson([
                'errors' => ['old_password' => true]
            ]);

        // Set the new password with incorrect old password
        $response = $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/me/password', [
                'algorithm' => $newAlgorithm,
                'old_password' => 'blabla',
                'password' => $newPassword
            ])
            ->assertJson([
                'errors' => ['old_password' => true]
            ])
            ->assertStatus(422);

        // Set the new password
        $this->keyAuthenticated($account)
            ->json($this->method, $this->route.'/me/password', [
                'algorithm' => $newAlgorithm,
                'old_password' => $password,
                'password' => $newPassword
            ])
            ->assertStatus(200);

        // Second check
        $this->keyAuthenticated($account)
            ->get($this->route.'/me')
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
            ->get($this->route.'/'.$password->account->id.'/deactivate')
            ->assertStatus(200)
            ->assertJson([
                'activated' => false
            ]);

        $this->keyAuthenticated($admin->account)
            ->get($this->route.'/'.$password->account->id)
            ->assertStatus(200)
            ->assertJson([
                'activated' => false
            ]);

        $this->keyAuthenticated($admin->account)
            ->get($this->route.'/'.$password->account->id.'/activate')
            ->assertStatus(200)
            ->assertJson([
                'activated' => true
            ]);

        $this->keyAuthenticated($admin->account)
            ->get($this->route.'/'.$password->account->id)
            ->assertStatus(200)
            ->assertJson([
                'activated' => true
            ]);
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
            ->get($this->route.'/'.$admin->id)
            ->assertStatus(200)
            ->assertJson([
                'id' => 1
            ]);
    }

    public function testDelete()
    {
        $password = Password::factory()->create();

        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $this->keyAuthenticated($admin->account)
            ->delete($this->route.'/'.$password->account->id)
            ->assertStatus(200);

        $this->keyAuthenticated($admin->account)
            ->get($this->route.'/'.$password->account->id)
            ->assertStatus(404);
    }
}
