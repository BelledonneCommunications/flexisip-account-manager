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

namespace Tests;

use App\PhoneCountry;
use App\Space;
use App\Http\Middleware\SpaceCheck;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use TestUtilsTrait;

    protected $route = '/api/accounts/me';
    protected $method = 'GET';

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([SpaceCheck::class]);

        config()->set('app.sip_domain', 'sip.example.com');

        PhoneCountry::truncate();
        PhoneCountry::factory()->france()->activated()->create();
        PhoneCountry::factory()->netherlands()->create();
    }

    protected function setSpaceOnRoute(Space $space, string $route)
    {
        return str_replace('localhost', $space->domain, $route);
    }
}
