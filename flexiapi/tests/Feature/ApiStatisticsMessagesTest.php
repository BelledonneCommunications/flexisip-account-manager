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

use App\Admin;
use App\StatisticsMessageDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiStatisticsMessagesTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected $route = '/api/statistics/messages';

    public function testMessages()
    {
        $admin = Admin::factory()->create();
        $admin->account->generateApiKey();

        $id = '1234';

        $this->keyAuthenticated($admin->account)
            ->json('POST', $this->route, [
                'id' => $id,
                'from' => $this->faker->email(),
                'sent_at' => $this->faker->iso8601(),
                'encrypted' => false
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('statistics_messages', [
            'id' => $id
        ]);

        $this->keyAuthenticated($admin->account)
            ->json('POST', $this->route, [
                'id' => $id,
                'from' => $this->faker->email(),
                'sent_at' => $this->faker->iso8601(),
                'encrypted' => false
            ])
            ->assertStatus(400);

        $this->keyAuthenticated($admin->account)
            ->json('POST', $this->route, [
                'id' => $id,
                'from' => $this->faker->email(),
                'sent_at' => 'bad_date',
                'encrypted' => false
            ])
            ->assertJsonValidationErrors(['sent_at']);

        // Patch previous message with devices

        $to = $this->faker->email();
        $device = $this->faker->uuid();

        $receivedAt = $this->faker->iso8601();
        $lastStatus = 200;

        $newReceivedAt = $this->faker->iso8601();
        $newLastStatus = 201;

        $this->keyAuthenticated($admin->account)
            ->json('PATCH', $this->route . '/' . $id . '/to/' . $to . ' /devices/' . $device, [
                'last_status' => $lastStatus,
                'received_at' => $receivedAt
            ])
            ->assertStatus(201);

        $this->keyAuthenticated($admin->account)
            ->json('PATCH', $this->route . '/' . $id . '/to/' . $to . ' /devices/' . $device, [
                'last_status' => $newLastStatus,
                'received_at' => $newReceivedAt
            ])
            ->assertStatus(200);

        $this->assertSame(1, StatisticsMessageDevice::count());
        $this->assertDatabaseHas('statistics_message_devices', [
            'message_id' => $id,
            'last_status' => $newLastStatus
        ]);

        $this->keyAuthenticated($admin->account)
            ->json('PATCH', $this->route . '/' . $id . '/to/' . $this->faker->email() . ' /devices/' . $this->faker->uuid(), [
                'last_status' => $newLastStatus,
                'received_at' => $newReceivedAt
            ])
            ->assertStatus(201);

        $this->assertSame(2, StatisticsMessageDevice::count());
    }
}
