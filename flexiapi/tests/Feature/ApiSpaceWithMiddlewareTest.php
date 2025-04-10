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
use Tests\TestCaseWithSpaceMiddleware;

class ApiSpaceWithMiddlewareTest extends TestCaseWithSpaceMiddleware
{
    protected $method = 'POST';
    protected $route = '/api/spaces';
    protected $accountRoute = '/api/accounts';

    public function testExpiredSpace()
    {
        $superAdmin = Account::factory()->superAdmin()->create();
        $superAdmin->generateUserApiKey();

        $username = 'username';

        $space = Space::factory()->secondDomain()->expired()->create();
        $admin = Account::factory()->fromSpace($space)->admin()->create();

        // Try to create a new user as an admin
        $admin->generateUserApiKey();
        config()->set('app.root_host', $admin->domain);

        space(reload: true);

        $this->keyAuthenticated($admin)
            ->json($this->method, 'http://' . $admin->domain .  $this->accountRoute, [
                'username' => 'new',
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])->assertStatus(403);

        // Unexpire the space and try again
        $space = $this->keyAuthenticated($superAdmin)
            ->get($this->route . '/' . $admin->domain)
            ->json();

        $space['expire_at'] = Carbon::tomorrow()->toDateTimeString();

        $this->keyAuthenticated($superAdmin)
            ->json('PUT', $this->route . '/' . $admin->domain, $space)
            ->assertStatus(200);

        space(reload: true);

        $this->keyAuthenticated($admin)
            ->json($this->method, $this->accountRoute, [
                'username' => 'new',
                'algorithm' => 'SHA-256',
                'password' => '123456',
            ])->assertStatus(200);
    }
}
