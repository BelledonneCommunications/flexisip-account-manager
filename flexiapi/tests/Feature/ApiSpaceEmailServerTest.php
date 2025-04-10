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

class ApiSpaceEmailServerTest extends TestCase
{
    protected $method = 'POST';
    protected $route = '/api/spaces';

    public function testEmailServer()
    {
        $admin = Account::factory()->superAdmin()->create();
        $admin->generateUserApiKey();
        $emailHost = 'email.domain';

        $route = $this->route . '/' . $admin->space->host . '/email';

        $this->keyAuthenticated($admin)
            ->json($this->method, $route, [
                'host' => $emailHost,
                'port' => 22
            ])
            ->assertStatus(201);

        $this->keyAuthenticated($admin)
            ->json('GET', $route)
            ->assertJsonFragment([
                'host' => $emailHost,
                'port' => 22
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json($this->method, $route, [
                'host' => $emailHost,
                'port' => 23
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('GET', $route)
            ->assertJsonFragment([
                'port' => 23
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('DELETE', $route)
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('GET', $route)
            ->assertStatus(404);
    }
}
