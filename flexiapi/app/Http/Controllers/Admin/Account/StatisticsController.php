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

namespace App\Http\Controllers\Admin\Account;

use App\Account;
use App\Enums\StatisticsGraphDirection;
use App\Enums\StatisticsGraphType;
use App\Http\Controllers\Controller;
use App\Libraries\StatisticsGraphFactory;
use App\StatisticsCall;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function edit(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);

        return redirect()->route('admin.account.statistics.show', [
            'account' => $account,
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'by' => $request->input('by'),
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
            'jsonConfig' => json_encode((new StatisticsGraphFactory($request))->getAccountGraphConfig(StatisticsGraphType::MESSAGES, $account->username, $account->domain, StatisticsGraphDirection::FROM)),
            'request' => $request
        ])->render();

        $messagesToGraph = view('parts.graph', [
            'jsonConfig' => json_encode((new StatisticsGraphFactory($request))->getAccountGraphConfig(StatisticsGraphType::MESSAGES, $account->username, $account->domain, StatisticsGraphDirection::TO)),
            'request' => $request
        ])->render();

        $callsFromGraph = view('parts.graph', [
            'jsonConfig' => json_encode((new StatisticsGraphFactory($request))->getAccountGraphConfig(StatisticsGraphType::CALLS, $account->username, $account->domain, StatisticsGraphDirection::FROM)),
            'request' => $request
        ])->render();

        $callsToGraph = view('parts.graph', [
            'jsonConfig' => json_encode((new StatisticsGraphFactory($request))->getAccountGraphConfig(StatisticsGraphType::CALLS, $account->username, $account->domain, StatisticsGraphDirection::TO)),
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

    public function editCallLog(Request $request, int $accountId)
    {
        return redirect()->route('admin.account.statistics.show_call_log', [
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'direction' => $request->input('direction'),
            'account' => $accountId,
        ]);
    }

    public function showCallLog(Request $request, int $accountId, ?bool $adminView = true)
    {
        $account = Account::findOrFail($accountId);
        $incomingCalls = StatisticsCall::query()->where('to_domain', $account->domain)
            ->where('to_username', $account->username);
        $outgoingCalls = StatisticsCall::query()->where('from_domain', $account->domain)
            ->where('from_username', $account->username);

        if ($request->input('to')) {
            $incomingCalls = $incomingCalls->where('initiated_at', '<=', $request->input('to'));
            $outgoingCalls = $outgoingCalls->where('initiated_at', '<=', $request->input('to'));
        }

        if ($request->input('from')) {
            $incomingCalls = $incomingCalls->where('initiated_at', '>=', $request->input('from'));
            $outgoingCalls = $outgoingCalls->where('initiated_at', '>=', $request->input('from'));
        }

        $calls = match ($request->input('direction')) {
            'incoming' => $incomingCalls,
            'outgoing' => $outgoingCalls,
            default => $outgoingCalls->union($incomingCalls),
        };

        return view('admin.account.statistics.show_call_log', [
            'account' => $account,
            'calls' => $calls->orderBy('initiated_at', 'desc')->paginate(30),
            'request' => $request,
            'admin_view' => $adminView
        ]);
    }
}
