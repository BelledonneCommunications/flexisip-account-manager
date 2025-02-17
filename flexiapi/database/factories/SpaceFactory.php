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

namespace Database\Factories;

use App\Space;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpaceFactory extends Factory
{
    protected $model = Space::class;

    public function definition()
    {
        return [
            'domain' => config('app.sip_domain'),
            'host' => config('app.sip_domain'),
        ];
    }

    public function local()
    {
        return $this->state(fn (array $attributes) => [
            'host' => 'localhost',
        ]);
    }

    public function withoutProvisioningHeader()
    {
        return $this->state(fn (array $attributes) => [
            'provisioning_use_linphone_provisioning_header' => false,
        ]);
    }

    public function secondDomain()
    {
        return $this->state(fn (array $attributes) => [
            'domain' => 'second_' . config('app.sip_domain'),
            'host' => 'second_' . config('app.sip_domain'),
        ]);
    }

    public function withRealm(string $realm)
    {
        return $this->state(fn (array $attributes) => [
            'account_realm' => $realm,
        ]);
    }

    public function expired()
    {
        return $this->state(fn (array $attributes) => [
            'expire_at' => Carbon::today()->toDateTimeString()
        ]);
    }
}
