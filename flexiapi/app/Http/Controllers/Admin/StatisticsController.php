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

namespace App\Http\Controllers\Admin;

use App\Account;
use App\ContactsList;
use App\StatisticsMessage;
use App\StatisticsCall;
use App\Http\Controllers\Controller;
use App\Libraries\StatisticsGraphFactory;

use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('admin.statistics.show', [
            'type' => 'messages'
        ]);
    }

    public function edit(Request $request)
    {
        return redirect()->route('admin.statistics.show', [
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'by' => $request->get('by'),
            'type' => $request->get('type'),
            'domain' => $request->get('domain'),
            'contacts_list' => $request->get('contacts_list'),
        ]);
    }

    public function show(Request $request, string $type = 'messages')
    {
        $request->validate([
            'from' => 'date_format:Y-m-d|before:to',
            'to' => 'date_format:Y-m-d|after:from',
            'by' => 'in:day,week,month,year',
        ]);

        $graph = new StatisticsGraphFactory($request, type: $type, domain: $request->get('domain'));
        $config = $graph->getConfig();
        $domains = collect();

        if ($request->get('export', false)) {
            return $graph->export();
        }

        if (config('app.admins_manage_multi_domains')) {
            switch ($type) {
                case 'messages':
                    $domains = StatisticsMessage::groupBy('from_domain')->pluck('from_domain');
                    break;

                case 'calls':
                    $domains = StatisticsCall::groupBy('from_domain')->pluck('from_domain');
                    break;

                case 'accounts':
                    $domains = Account::groupBy('domain')->pluck('domain');
                    break;
            }
        }

        return view('admin.statistics.show', [
            'domains' => $domains,
            'contacts_lists' => ContactsList::all()->pluck('title', 'id'),
            'jsonConfig' => json_encode($config),
            'type' => $type,
            'request' => $request
        ]);
    }

    public function editCallLogs(Request $request)
    {
        return redirect()->route('admin.statistics.show_call_logs', [
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'domain' => $request->get('domain'),
            'contacts_list' => $request->get('contacts_list'),
        ]);
    }

    public function showCallLogs(Request $request)
    {
        $fromQuery = StatisticsCall::query();
        $toQuery = StatisticsCall::query();

        if ($request->get('domain')) {
            $fromQuery->where('to_domain', $request->get('domain'));
            $toQuery->where('from_domain', $request->get('domain'));
        }

        if ($request->get('to')) {
            $fromQuery->where('initiated_at', '<=', $request->get('to'));
            $toQuery->where('initiated_at', '<=', $request->get('to'));
        }

        if ($request->get('from')) {
            $fromQuery->where('initiated_at', '>=', $request->get('from'));
            $toQuery->where('initiated_at', '>=', $request->get('from'));
        }

        if ($request->has('contacts_list')) {
            $fromQuery->fromByContactsList($request->get('contacts_list'));
            $toQuery->toByContactsList($request->get('contacts_list'));
        }

        $calls = $fromQuery->union($toQuery);

        return view('admin.statistics.show_call_logs', [
            'calls' => $calls->orderBy('initiated_at', 'desc')->paginate(30),
            'domains' => StatisticsCall::groupBy('from_domain')->pluck('from_domain'),
            'contacts_lists' => ContactsList::all()->pluck('title', 'id'),
            'request' => $request,
        ]);
    }
}
