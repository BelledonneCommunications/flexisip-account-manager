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

use App\InviteTerminatedState;
use App\StatisticsCall;
use DateInterval;
use Illuminate\Database\Eloquent\Factories\Factory;
use Awobaz\Compoships\Database\Eloquent\Factories\ComposhipsFactory;

class StatisticsCallDeviceFactory extends Factory
{
    use ComposhipsFactory;

    public function definition(): array
    {
        $rangAt = $this->faker->dateTimeBetween('-1 year');

        return [
            'call_id' => StatisticsCall::factory(),
            'device_id' => $this->faker->uuid(),
            'rang_at' => $rangAt,
            'invite_terminated_at' => $this->faker->dateTimeBetween($rangAt, (clone $rangAt)->add(new DateInterval("PT1H"))),
            'invite_terminated_state' => InviteTerminatedState::values()[
                array_rand(InviteTerminatedState::values())
            ],
        ];
    }
}
