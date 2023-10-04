<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Http\Controllers\Controller;
use App\Libraries\StatisticsGraphFactory;
use Illuminate\Http\Request;

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
}
