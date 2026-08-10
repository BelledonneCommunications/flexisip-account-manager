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

use App\Account;
use App\Space;
use App\SpaceDigestAuthenticationConfiguration;
use App\SpaceOIDCAuthenticationConfiguration;
use Tests\TestCase;

class ApiSpaceAuthenticationConfigurationTest extends TestCase
{
    protected $spaceRoute = '/api/spaces';

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testOIDCConfiguration()
    {
        $this->generateKeyPair();
        $space = Space::factory()->create();
        $oidcConfiguration = SpaceOIDCAuthenticationConfiguration::factory()->withSpaceId($space->id)->create([
            'public_key' => $this->serverPublicKeyPem,
        ]);

        $admin = Account::factory()->superAdmin()->create();
        $admin->generateUserApiKey();

        $this->keyAuthenticated($admin)
            ->get($this->spaceRoute . '/' . $space->domain)
            ->assertJsonFragment(['server_url' => $oidcConfiguration->server_url]);
    }

    public function testDigestConfiguration()
    {
        $space = Space::factory()->create();
        $digestConfiguration = SpaceDigestAuthenticationConfiguration::factory()->withSpaceId($space->id)->create();

        $admin = Account::factory()->superAdmin()->create();
        $admin->generateUserApiKey();

        $this->keyAuthenticated($admin)
            ->get($this->spaceRoute . '/' . $space->domain)
            ->assertJsonFragment([
                'realm' => $digestConfiguration->realm,
                'default_password_algorithm' => $digestConfiguration->default_password_algorithm
            ]);
    }
}
