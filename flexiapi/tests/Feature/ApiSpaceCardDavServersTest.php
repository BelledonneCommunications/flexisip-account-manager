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
use App\SpaceCardDavServer;
use Carbon\Carbon;
use Tests\TestCase;

class ApiSpaceCardDavServersTest extends TestCase
{
    protected $spaceRoute = '/api/spaces';
    protected $accountRoute = '/api/accounts';

    public function testCardDavServerAdminForbidden()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $route = $this->spaceRoute . '/' . $admin->space->domain . '/carddavs';
        $uri = 'http://test.com';

        $this->keyAuthenticated($admin)
            ->json('POST', $route, [
                'uri' => $uri
            ])
            ->assertStatus(403);
    }

    public function testCardDavServerCrud()
    {
        $superAdmin = Account::factory()->superAdmin()->create();
        $superAdmin->generateUserApiKey();

        $route = $this->spaceRoute . '/' . $superAdmin->space->domain . '/carddavs';

        $uri = 'http://test.com';
        $uri2 = 'http://test2.com';

        // Test with a standard admin first

        $superAdmin->space->super = false;
        $superAdmin->space->save();

        $this->keyAuthenticated($superAdmin)
            ->json('GET', $route)
            ->assertStatus(403);

        $superAdmin->space->super = true;
        $superAdmin->space->save();

        // Super Admin again

        $this->keyAuthenticated($superAdmin)
            ->json('GET', $route)
            ->assertJson([])
            ->assertStatus(200);

        $this->keyAuthenticated($superAdmin)
            ->json('POST', $route, [
                'uri' => $uri
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($superAdmin)
            ->json('GET', $route)
            ->assertJsonFragment([
                'uri' => $uri,
                'enabled' => false
            ])
            ->assertStatus(200);

        $cardDavServer = SpaceCardDavServer::first();

        $this->keyAuthenticated($superAdmin)
            ->json('GET', $route . '/' . $cardDavServer->id)
            ->assertJsonFragment([
                'uri' => $uri,
                'enabled' => false
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($superAdmin)
            ->json('PUT', $route. '/' . $cardDavServer->id, [
                'uri' => $uri2,
                'enabled' => true
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($superAdmin)
            ->json('PUT', $route. '/' . $cardDavServer->id, [
                'uri' => $uri2,
                'enabled' => true,
                'fields_for_domain' => 'wrong _ data'
            ])->assertJsonValidationErrors(['fields_for_domain']);

        $this->keyAuthenticated($superAdmin)
            ->json('GET', $route . '/' . $cardDavServer->id)
            ->assertJsonFragment([
                'uri' => $uri2,
                'enabled' => true
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($superAdmin)
            ->json('DELETE', $route . '/' . $cardDavServer->id)
            ->assertStatus(200);

        $this->keyAuthenticated($superAdmin)
            ->json('GET', $route . '/' . $cardDavServer->id)
            ->assertStatus(404);

        $this->keyAuthenticated($superAdmin)
            ->json('GET', $route)
            ->assertJson([])
            ->assertStatus(200);
    }

    public function testCardDavServerOtherAdminForbidden()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $secondSpace = Space::factory()->secondDomain()->create();
        $secondAdmin = Account::factory()->admin()->fromSpace($secondSpace)->create();
        $secondAdmin->generateUserApiKey();

        $superAdmin = Account::factory()->superAdmin()->create();
        $superAdmin->generateUserApiKey();

        $credentials = [
            'username' => 'john',
            'realm' => 'hop.com',
            'password' => '1234',
            'algorithm' => 'MD5'
        ];

        $route = $this->spaceRoute . '/' . $admin->space->domain . '/carddavs';

        // Creating the CardDav
        $this->keyAuthenticated($superAdmin)
            ->json('POST', $route, [
                'uri' => 'http://server',
            ])
            ->assertStatus(200);

        // Allowing CardDav credentials for Admin 1 space
        $server = $this->keyAuthenticated($admin)
            ->json('GET', $this->spaceRoute . '/' . $admin->space->domain)
            ->assertStatus(200)
            ->json();

        $server['carddav_user_credentials'] = true;

        $this->keyAuthenticated($admin)
            ->json('PUT', $this->spaceRoute . '/' . $admin->space->domain, $server)
            ->assertStatus(200);

        // First Admin can get its own credentials
        $this->keyAuthenticated($admin)
            ->json('GET', $this->accountRoute . '/' . $admin->id . '/carddavs')
            ->assertStatus(200);

        // The other Admin cannot get it
        $this->keyAuthenticated($secondAdmin)
            ->json('GET', $this->accountRoute . '/' . $admin->id . '/carddavs')
            ->assertStatus(403);
    }

    public function testCardDavCredentials()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateUserApiKey();

        $user = Account::factory()->create();
        $user->generateUserApiKey();

        $route = $this->accountRoute . '/' . $user->id . '/carddavs';

        $this->keyAuthenticated($admin)
            ->json('GET', $route)
            ->assertStatus(403);

        Space::where('domain', $user->domain)->update(['carddav_user_credentials' => true]);

        $this->keyAuthenticated($admin)
            ->json('GET', $route)
            ->assertSee('{}')
            ->assertStatus(200);

        // Create the CardDav server

        Space::where('domain', $user->domain)->update(['super' => true]);

        $this->keyAuthenticated($admin)
            ->json('POST', $this->spaceRoute . '/' . $admin->space->domain . '/carddavs', [
                'uri' => 'http://uri.com'
            ])
            ->assertStatus(200);

        Space::where('domain', $user->domain)->update(['super' => false]);

        $cardDavServer = SpaceCardDavServer::first();

        // Create the credentials

        $credentials = [
            'username' => 'john',
            'realm' => 'hop.com',
            'password' => '1234',
            'algorithm' => 'MD5'
        ];

        $this->keyAuthenticated($admin)
            ->json('PUT', $route . '/431' , $credentials)
            ->assertStatus(404);

        $this->keyAuthenticated($admin)
            ->json('PUT', $route . '/' . $cardDavServer->id , $credentials)
            ->assertStatus(200);

        $credentials['realm'] = 'hop2.com';

        // Again
        $this->keyAuthenticated($admin)
            ->json('PUT', $route . '/' . $cardDavServer->id , $credentials)
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('GET', $route)
            ->assertJsonFragment([
                'username' => $credentials['username'],
            ])
            ->assertJsonFragment([
                'realm' => $credentials['realm'],
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('GET', $route . '/' . $cardDavServer->id)
            ->assertJsonFragment([
                'username' => $credentials['username'],
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('DELETE', $route . '/' . $cardDavServer->id)
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('GET', $route . '/' . $cardDavServer->id)
            ->assertStatus(404);

        $this->keyAuthenticated($admin)
            ->json('GET', $route)
            ->assertSee('{}')
            ->assertStatus(200);
    }
}
