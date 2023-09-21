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
use App\StatisticsCall;
use App\StatisticsCallDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatisticsCallController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|string|max:64',
            'from' => 'required|string|max:256',
            'to' => 'required|string|max:256',
            'initiated_at' => 'required|iso_date',
            'ended_at' => 'iso_date|nullable',
            'conference_id' => 'string|nullable',
        ]);

        $statisticsCall = new StatisticsCall;
        $statisticsCall->id = $request->get('id');
        list($statisticsCall->from_username, $statisticsCall->from_domain) = explode('@', $request->get('from'));
        list($statisticsCall->to_username, $statisticsCall->to_domain) = explode('@', $request->get('to'));
        $statisticsCall->initiated_at = $request->get('initiated_at');
        $statisticsCall->ended_at = $request->get('ended_at');
        //$statisticsCall->conference_id = $request->get('conference_id');

        try {
            return $statisticsCall->saveOrFail();
        } catch (\Exception $e) {
            Log::channel('database_errors')->error($e->getMessage());
            abort(400, 'Database error');
        }
    }

    public function storeDevice(Request $request, string $callId, string $deviceId)
    {
        $request->validate([
            'rang_at' => 'iso_date',
            'invite_terminated.at' => 'required_with:invite_terminated.state,iso_date',
            'invite_terminated.state' => 'required_with:invite_terminated.at,string',
        ]);

        try {
            return StatisticsCallDevice::updateOrCreate(
                ['call_id' => $callId, 'device_id' => $deviceId],
                [
                    'rang_at' => $request->get('rang_at'),
                    'invite_terminated_at' => $request->get('invite_terminated.at'),
                    'invite_terminated_state' => $request->get('invite_terminated.state')
                ]
            );
        } catch (\Exception $e) {
            Log::channel('database_errors')->error($e->getMessage());
            abort(400, 'Database error');
        }
    }

    public function update(Request $request, string $callId)
    {
        $request->validate([
            'ended_at' => 'required|iso_date',
        ]);

        $statisticsCall = StatisticsCall::where('id', $callId)->firstOrFail();
        $statisticsCall->ended_at = $request->get('ended_at');
        $statisticsCall->save();

        return $statisticsCall;
    }
}
