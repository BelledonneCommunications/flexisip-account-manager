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

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\StatisticsMessage;
use App\StatisticsMessageDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatisticsMessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|string|max:64',
            'from' => 'required|string|max:256',
            'sent_at' => 'required|iso_date',
            'encrypted' => 'required|boolean',
            'conference_id' => 'string|nullable',
        ]);

        $statisticsMessage = new StatisticsMessage;
        $statisticsMessage->id = $request->get('id');
        list($statisticsMessage->from_username, $statisticsMessage->from_domain) = explode('@', $request->get('from'));
        $statisticsMessage->sent_at = $request->get('sent_at');
        $statisticsMessage->encrypted = $request->get('encrypted');
        //$statisticsMessage->conference_id = $request->get('conference_id');

        try {
            return $statisticsMessage->saveOrFail();
        } catch (\Exception $e) {
            Log::channel('database_errors')->error($e->getMessage());
            abort(400, 'Database error');
        }
    }

    public function storeDevice(Request $request, string $messageId, string $to, string $deviceId)
    {
        $request->validate([
            // We don't validate the message_id to avoid a specific DB request, the foreign key constraint is taking care of it
            'last_status' => 'required|integer',
            'received_at' => 'required|iso_date'
        ]);

        list($toUsername, $toDomain) = explode('@', $to);

        try {
            return StatisticsMessageDevice::updateOrCreate(
                ['message_id' => $messageId, 'to_username' => $toUsername, 'to_domain' => $toDomain, 'device_id' => $deviceId],
                ['last_status' => $request->get('last_status'), 'received_at' => $request->get('received_at')]
            );
        } catch (\Exception $e) {
            Log::channel('database_errors')->error($e->getMessage());
            abort(400, 'Database error');
        }
    }
}
