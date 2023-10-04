<?php

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

    /*public function search(Request $request)
    {
        return redirect()->route('admin.statistics.search', $request->except('_token', 'query'));
    }*/

    public function show(Request $request, string $type = 'messages')
    {
        $request->validate([
            'from' => 'date_format:Y-m-d|before:to',
            'to' => 'date_format:Y-m-d|after:from',
            'by' => 'in:day,week,month,year',
        ]);

        $graph = new StatisticsGraphFactory($request, $type, fromDomain: $request->get('domain'));
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
}
