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

use App\StatisticsMessage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Awobaz\Compoships\Database\Eloquent\Factories\ComposhipsFactory;

class StatisticsMessageDeviceFactory extends Factory
{
    use ComposhipsFactory;

    public function definition(): array
    {
        $message = StatisticsMessage::factory()->create();

        return [
            'message_id' => $message->id,
            'to_username' => $this->faker->userName(),
            'to_domain' => $this->faker->domainName(),
            'device_id' => $this->faker->uuid(),
            'received_at' => $this->faker->dateTimeBetween('-1 year'),
            'last_status' => 200,
        ];
    }
}
