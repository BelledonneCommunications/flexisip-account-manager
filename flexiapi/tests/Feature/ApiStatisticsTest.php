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
use App\StatisticsCallDevice;
use App\StatisticsMessageDevice;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiStatisticsTest extends TestCase
{
    use WithFaker;

    protected $routeMessages = '/api/statistics/messages';
    protected $routeCalls = '/api/statistics/calls';

    public function testMessages()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateApiKey();

        $id = '1234';
        $fromUsername = 'username';
        $fromDomain = 'domain.com';

        $account = Account::factory()->create([
            'username' => $fromUsername,
            'domain' => $fromDomain,
        ]);

        $this->keyAuthenticated($admin)
            ->json('POST', $this->routeMessages, [
                'id' => $id,
                'from' => $fromUsername . '@' . $fromDomain,
                'sent_at' => $this->faker->iso8601(),
                'encrypted' => false
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('statistics_messages', [
            'id' => $id
        ]);

        $this->keyAuthenticated($admin)
            ->json('POST', $this->routeMessages, [
                'id' => $id,
                'from' => $this->faker->email(),
                'sent_at' => $this->faker->iso8601(),
                'encrypted' => false
            ])
            ->assertStatus(400);

        $this->keyAuthenticated($admin)
            ->json('POST', $this->routeMessages, [
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

        $this->keyAuthenticated($admin)
            ->json('PATCH', $this->routeMessages . '/' . $id . '/to/' . $to . ' /devices/' . $device, [
                'last_status' => $lastStatus,
                'received_at' => $receivedAt
            ])
            ->assertStatus(201);

        $this->keyAuthenticated($admin)
            ->json('PATCH', $this->routeMessages . '/' . $id . '/to/' . $to . ' /devices/' . $device, [
                'last_status' => $newLastStatus,
                'received_at' => $newReceivedAt
            ])
            ->assertStatus(200);

        $this->assertSame(1, StatisticsMessageDevice::count());
        $this->assertDatabaseHas('statistics_message_devices', [
            'message_id' => $id,
            'last_status' => $newLastStatus
        ]);

        $this->keyAuthenticated($admin)
            ->json('PATCH', $this->routeMessages . '/' . $id . '/to/' . $this->faker->email() . ' /devices/' . $this->faker->uuid(), [
                'last_status' => $newLastStatus,
                'received_at' => $newReceivedAt
            ])
            ->assertStatus(201);

        $this->assertSame(2, StatisticsMessageDevice::count());

        // Deletion event test

        $account->delete();
        $this->assertDatabaseMissing('statistics_messages', [
            'id' => $id
        ]);
    }

    public function testCalls()
    {
        $admin = Account::factory()->admin()->create();
        $admin->generateApiKey();

        $id = '1234';
        $fromUsername = 'username';
        $fromDomain = 'domain.com';
        $toUsername = 'usernameto';
        $toDomain = 'domainto.com';

        $account = Account::factory()->create([
            'username' => $fromUsername,
            'domain' => $fromDomain,
        ]);

        $this->keyAuthenticated($admin)
            ->json('POST', $this->routeCalls, [
                'id' => $id,
                'from' => $fromUsername . '@' . $fromDomain,
                'to' => $toUsername . '@' . $toDomain,
                'initiated_at' => $this->faker->iso8601(),
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('statistics_calls', [
            'id' => $id
        ]);

        $this->keyAuthenticated($admin)
            ->json('POST', $this->routeCalls, [
                'id' => $id,
                'from' => $fromUsername . '@' . $fromDomain,
                'to' => $toUsername . '@' . $toDomain,
                'initiated_at' => $this->faker->iso8601(),
            ])
            ->assertStatus(400);

        // Patch previous call with devices*

        $to = $this->faker->email();
        $device = $this->faker->uuid();

        $rangAt = $this->faker->iso8601();
        $newRangAt = $this->faker->iso8601();

        $this->keyAuthenticated($admin)
            ->json('PATCH', $this->routeCalls . '/' . $id . '/devices/' . $device, [
                'rang_at' => $rangAt,
                'invite_terminated' => [
                    'at' => $this->faker->iso8601(),
                    'state' => 'declined'
                ]
            ])
            ->assertStatus(201);

        $this->keyAuthenticated($admin)
            ->json('PATCH', $this->routeCalls . '/' . $id . '/devices/' . $device, [
                'rang_at' => $newRangAt,
                'invite_terminated' => [
                    'at' => $this->faker->iso8601(),
                    'state' => 'declined'
                ]
            ])
            ->assertStatus(200);

        $this->keyAuthenticated($admin)
            ->json('PATCH', $this->routeCalls . '/' . $id . '/devices/' . $device, [
                'invite_terminated' => [
                    'state' => 'declined'
                ]
            ])
            ->assertStatus(422);

        $this->keyAuthenticated($admin)
            ->json('PATCH', $this->routeCalls . '/' . $id . '/devices/' . $device, [
                'rang_at' => $this->faker->iso8601()
            ])
            ->assertStatus(200);

        $this->assertSame(1, StatisticsCallDevice::count());

        // Update

        $endedAt = $this->faker->iso8601();

        $this->keyAuthenticated($admin)
            ->json('PATCH', $this->routeCalls . '/' . $id, [
                'ended_at' => $endedAt
            ])
            ->assertStatus(200);

        // Deletion event test

        $account->delete();
        $this->assertDatabaseMissing('statistics_calls', [
            'id' => $id
        ]);
    }
}
