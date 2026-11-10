<?php

/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2026 Belledonne Communications SARL, All rights reserved.

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

use App\AuthToken;
use App\Password;
use App\Space;
use Database\Factories\PasswordFactory;
use Tests\TestCase;

class AccountLoginTest extends TestCase
{
    public function testUnauthenticatedRequestIsRedirectedToLogin()
    {
        Password::factory()->create();

        $this->get('/dashboard')->assertRedirect('login');
        $this->get('/admin/spaces/me')->assertRedirect('login');
    }

    public function testUnauthenticatedJsonRequestIsNotRedirected()
    {
        Password::factory()->create();

        $this->getJson('/dashboard')->assertUnauthorized();
    }

    public function testLoginRedirectsToTheIntendedPage()
    {
        $password = Password::factory()->create();

        $this->get('/statistics/call_logs?direction=1&page=2')->assertRedirect('login');

        $this->followingRedirects()->post('/authenticate', [
            'username' => $password->account->username,
            'password' => PasswordFactory::TEST_PASSWORD,
        ]);

        $this->assertSame('/statistics/call_logs?direction=1&page=2', request()->getRequestUri());
    }

    public function testLoginRedirectsToTheDashboardWhenNoPageWasIntended()
    {
        $password = Password::factory()->create();

        $this->followingRedirects()->post('/authenticate', [
            'username' => $password->account->username,
            'password' => PasswordFactory::TEST_PASSWORD,
        ]);

        $this->assertSame('/dashboard', request()->getRequestUri());
    }

    public function testSuperAdminLoginRedirectsToTheSpacesPage()
    {
        $password = Password::factory()->admin()->create();

        $space = Space::where('domain', $password->account->domain)->first();
        $space->super = true;
        $space->save();

        $this->followingRedirects()->post('/authenticate', [
            'username' => $password->account->username,
            'password' => PasswordFactory::TEST_PASSWORD,
        ]);

        $this->assertSame('/admin/spaces', request()->getRequestUri());
    }

    public function testAdminLoginRedirectsToTheIntendedPage()
    {
        $password = Password::factory()->admin()->create();

        $this->get('/admin/spaces/me')->assertRedirect('login');

        $this->followingRedirects()->post('/authenticate', [
            'username' => $password->account->username,
            'password' => PasswordFactory::TEST_PASSWORD,
        ]);

        $this->assertSame('/admin/spaces/me', request()->getRequestUri());
    }

    public function testAuthTokenAuthenticationRedirectsToTheIntendedPage()
    {
        $password = Password::factory()->create();

        $authToken = new AuthToken;
        $authToken->token = 'auth_token_login_test';
        $authToken->account_id = $password->account->id;
        $authToken->save();

        $this->get('/dashboard?direction=1')->assertRedirect('login');

        $this->followingRedirects()->get('/auth_tokens/auth/' . $authToken->token);

        $this->assertSame('/dashboard?direction=1', request()->getRequestUri());
    }
}
