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
use App\StatisticsCall;
use Tests\TestCase;

class CallLogsTest extends TestCase
{
    public function testDateFilter()
    {
        $to = '2026-01-31';
        $from = '2026-01-01';
        $account = Account::factory()->create();
        $factory = StatisticsCall::factory();

        $incomingInRange = $factory->toAccount($account)->createMany([
            ['initiated_at' => '2026-01-15 10:00:00'],
            ['initiated_at' => '2026-01-25 10:00:00']
        ])->modelKeys();

        // Out of range incoming call.
        $factory->toAccount($account)->create(['initiated_at' => '2028-07-14 10:00:00']);
        $outgoingInRange = $factory->fromAccount($account)->createMany([
            ['initiated_at' => '2026-01-16 10:00:00']
        ])->modelKeys();

        // Out of range outgoing call.
        $factory->fromAccount($account)->create(['initiated_at' => '2026-02-05 10:00:00']);

        // Calls between unrelated accounts, deliberately within the date range,
        // to prove they are excluded by direction rather than by date.
        $factory->createMany([
            ['initiated_at' => '2026-01-12 10:00:00'],
            ['initiated_at' => '2026-01-18 10:00:00']
        ]);

        $isIncoming = fn ($call) => $call->to === $account->identifier;
        $isOutgoing = fn ($call) => $call->from === $account->identifier;
        $ids = fn ($calls) => $calls->getCollection()->pluck('id')->all();

        // Both directions within the date range.
        $response = $this->actingAs($account)
            ->get(route('account.statistics.show_call_logs', [
                'from' => $from,
                'to' => $to
            ]))
            ->assertOk();

        $calls = $response->viewData('calls');
        $this->assertCount(3, $calls);

        // Test that entries are actually either incoming or outgoing calls for the account.
        $this->assertEqualsCanonicalizing(array_merge($incomingInRange, $outgoingInRange), $ids($calls));
        $this->assertTrue($calls->every(fn ($call) => $isIncoming($call) || $isOutgoing($call)));

        // 'incoming' direction.
        $response = $this->actingAs($account)
            ->get(route('account.statistics.show_call_logs', [
                'from' => $from,
                'to' => $to,
                'direction' => 'incoming'
            ]))
            ->assertOk();

        $calls = $response->viewData('calls');
        $this->assertCount(2, $calls);

        // Test that entries are actually incoming calls for the account.
        $this->assertEqualsCanonicalizing($incomingInRange, $ids($calls));
        $this->assertTrue($calls->every($isIncoming));

        // 'outgoing' direction.
        $response = $this->actingAs($account)
            ->get(route('account.statistics.show_call_logs', [
                'from' => $from,
                'to' => $to,
                'direction' => 'outgoing'
            ]))
            ->assertOk();

        $calls = $response->viewData('calls');
        $this->assertCount(1, $calls);

        // Test that entries are actually outgoing calls for the account.
        $this->assertEqualsCanonicalizing($outgoingInRange, $ids($calls));
        $this->assertTrue($calls->every($isOutgoing));
    }
}
