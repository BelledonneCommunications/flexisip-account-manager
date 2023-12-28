<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2023 Belledonne Communications SARL, All rights reserved.

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

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Http\Controllers\Controller;
use App\Libraries\StatisticsGraphFactory;
use App\StatisticsCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountStatisticsController extends Controller
{
    public function edit(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);

        return redirect()->route('admin.account.statistics.show', [
            'account' => $account,
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'by' => $request->get('by'),
        ]);
    }

    public function show(Request $request, int $accountId)
    {
        $request->validate([
            'from' => 'date_format:Y-m-d|before:to',
            'to' => 'date_format:Y-m-d|after:from',
            'by' => 'in:day,week,month,year',
        ]);

        $account = Account::findOrFail($accountId);

        $messagesFromGraph = view('parts.graph', [
            'jsonConfig' => json_encode((new StatisticsGraphFactory($request, 'messages', fromUsername: $account->username, fromDomain: $account->domain))->getConfig()),
            'request' => $request
        ])->render();

        $messagesToGraph = view('parts.graph', [
            'jsonConfig' => json_encode((new StatisticsGraphFactory($request, 'messages', toUsername: $account->username, toDomain: $account->domain))->getConfig()),
            'request' => $request
        ])->render();

        $callsFromGraph = view('parts.graph', [
            'jsonConfig' => json_encode((new StatisticsGraphFactory($request, 'calls', fromUsername: $account->username, fromDomain: $account->domain))->getConfig()),
            'request' => $request
        ])->render();

        $callsToGraph = view('parts.graph', [
            'jsonConfig' => json_encode((new StatisticsGraphFactory($request, 'calls', toUsername: $account->username, toDomain: $account->domain))->getConfig()),
            'request' => $request
        ])->render();

        return view('admin.account.statistics.show', [
            'account' => $account,
            'messagesFromGraph' => $messagesFromGraph,
            'messagesToGraph' => $messagesToGraph,
            'callsFromGraph' => $callsFromGraph,
            'callsToGraph' => $callsToGraph,
        ]);
    }

    public function editCallLogs(Request $request, int $accountId)
    {
        return redirect()->route('admin.account.statistics.show_call_logs', [
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'account' => $accountId
        ]);
    }

    public function showCallLogs(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $toQuery = DB::table('statistics_calls')
            ->where('to_domain', $account->domain)
            ->where('to_username', $account->username);
        $calls = StatisticsCall::where('from_domain', $account->domain)
            ->where('from_username', $account->username);

        if ($request->get('to')) {
            $toQuery = $toQuery->where('initiated_at', '<=', $request->get('to'));
            $calls = $calls->where('initiated_at', '<=', $request->get('to'));
        }

        if ($request->get('from')) {
            $toQuery = $toQuery->where('initiated_at', '>=', $request->get('from'));
            $calls = $calls->where('initiated_at', '>=', $request->get('from'));
        }

        $calls = $calls->union($toQuery);

        return view('admin.account.statistics.show_call_logs', [
            'account' => $account,
            'calls' => $calls->orderBy('initiated_at', 'desc')->paginate(30),
            'request' => $request,
        ]);
    }
}
