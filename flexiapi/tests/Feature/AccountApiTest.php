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
                'activated' => false,
            ]);
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
            ]);;
    }
}
