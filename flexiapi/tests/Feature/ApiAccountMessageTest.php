<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2022 Belledonne Communications SARL, All rights reserved.

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
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ApiAccountMessageTest extends TestCase
{
    protected $route = '/api/messages';
    protected $method = 'POST';

    public function testRequest()
    {
        $password = Password::factory()->admin()->create();
        $password->account->generateApiKey();

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route, [
                'to' => '+badid',
                'body' => 'foobar'
            ])
            ->assertStatus(422);

        $this->keyAuthenticated($password->account)
            ->json($this->method, $this->route, [
                'to' => 'username@sip.linphone.org',
                'body' => 'Message content'
            ])
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->has('id');
            });
    }
}
