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

namespace App\Libraries;

use App\Account;
use App\StatisticsCall;
use App\StatisticsMessage;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsGraphFactory
{
    private $data = null;

    public function __construct(
        private Request $request,
        private string $type = 'messages',
        public ?string $domain = null, // both from and to filter
        public ?string $fromUsername = null,
        public ?string $fromDomain = null,
        public ?string $toUsername = null,
        public ?string $toDomain = null
    ) {
    }

    public function getConfig()
    {
        $dateColumn = 'created_at';
        $label = 'Label';

        switch ($this->type) {
            case 'messages':
                $dateColumn = 'sent_at';
                $label = 'Messages';
                $fromQuery = StatisticsMessage::query();
                $toQuery = StatisticsMessage::query();

                if (!Auth::user()?->admin) {
                    $fromQuery->where('from_domain', space()->domain);
                    $toQuery->toDomain($this->domain);
                } elseif ($this->domain) {
                    $fromQuery->where('from_domain', $this->domain);
                    $toQuery->toDomain($this->domain);
                } elseif ($this->fromDomain) {
                    $fromQuery->where('from_domain', $this->fromDomain)->orderBy('from_domain');

                    if ($this->fromUsername) {
                        $fromQuery->where('from_username', $this->fromUsername);
                    }
                } elseif ($this->toDomain && $this->toUsername) {
                    $toQuery->toUsernameDomain($this->toUsername, $this->toDomain);
                }

                if ($this->request->has('contacts_list')) {
                    $fromQuery = $fromQuery->fromByContactsList($this->request->get('contacts_list'));
                    $toQuery = $toQuery->toByContactsList($this->request->get('contacts_list'));
                }

                $this->data = $fromQuery; //->union($toQuery);
                $this->data->orderBy($dateColumn, 'asc');

                break;

            case 'calls':
                $dateColumn = 'initiated_at';
                $label = 'Calls';
                $fromQuery = StatisticsCall::query();
                $toQuery = StatisticsCall::query();

                if (!Auth::user()?->superAdmin) {
                    $fromQuery->where('from_domain', space()->domain);
                    $toQuery->where('to_domain', space()->domain);
                } elseif ($this->domain) {
                    $fromQuery = $fromQuery->where('to_domain', $this->domain);
                    $toQuery = $toQuery->where('from_domain', $this->domain);
                } elseif ($this->fromDomain) {
                    $fromQuery->where('from_domain', $this->fromDomain)->orderBy('from_domain');

                    if ($this->fromUsername) {
                        $fromQuery->where('from_username', $this->fromUsername);
                    }
                } elseif ($this->toDomain) {
                    $toQuery->where('to_domain', $this->toDomain)->orderBy('to_domain');

                    if ($this->toUsername) {
                        $toQuery->where('to_username', $this->toUsername);
                    }
                }

                if ($this->request->has('contacts_list')) {
                    $fromQuery = $fromQuery->fromByContactsList($this->request->get('contacts_list'));
                    $toQuery = $toQuery->toByContactsList($this->request->get('contacts_list'));
                }

                $this->data = $fromQuery; //->union($toQuery);
                $this->data->orderBy($dateColumn, 'asc');

                break;

            case 'accounts':
                $label = 'Accounts';
                $this->data = Account::orderBy($dateColumn, 'asc');

                // Accounts doesn't have a from and to
                $this->domain = $this->domain ?? $this->fromDomain;

                if (!Auth::user()?->admin) {
                    $this->data->where('domain', space()->domain);
                } elseif ($this->domain) {
                    $this->data->where('domain', $this->domain);

                    if ($this->fromUsername) {
                        $this->data->where('username', $this->fromUsername);
                    }
                }

                if ($this->request->has('contacts_list')) {
                    $this->data->whereIn('id', function ($query) {
                        $query->select('contact_id')
                            ->from('contacts_list_contact')
                            ->where('contacts_list_id', $this->request->get('contacts_list'));
                    });
                }

                break;
        }

        $this->data = $this->data->groupBy('moment')
            ->orderBy('moment', 'desc')
            ->setEagerLoads([]);

        if ($this->request->get('to')) {
            $this->data = $this->data->where($dateColumn, '<=', $this->request->get('to'));
        }

        $by = $this->request->get('by', 'day');

        switch ($by) {
            case 'day':
                $this->data = $this->data->where($dateColumn, '>=', $this->request->get('from', Carbon::now()->subDay()->format('Y-m-d H:i:s')))
                    ->get([
                        DB::raw("date_format(" . $dateColumn . ",'%Y-%m-%d %H') as moment"),
                        DB::raw('COUNT(*) as "count"')
                    ]);
                break;
            case 'week':
                $this->data = $this->data->where($dateColumn, '>=', $this->request->get('from', Carbon::now()->subWeek()->format('Y-m-d H:i:s')))
                    ->get([
                        DB::raw("date_format(" . $dateColumn . ",'%Y-%m-%d') as moment"),
                        DB::raw('COUNT(*) as "count"')
                    ]);
                break;
            case 'month':
                $this->data = $this->data->where($dateColumn, '>=', $this->request->get('from', Carbon::now()->subMonth()->format('Y-m-d H:i:s')))
                    ->get([
                        DB::raw("date_format(" . $dateColumn . ",'%Y-%m-%d') as moment"),
                        DB::raw('COUNT(*) as "count"')
                    ]);
                break;
            case 'year':
                $this->data = $this->data->where($dateColumn, '>=', $this->request->get('from', Carbon::now()->subYear()->format('Y-m-d H:i:s')))
                    ->get([
                        DB::raw("date_format(" . $dateColumn . ",'%Y-%m') as moment"),
                        DB::raw('COUNT(*) as "count"')
                    ]);
                break;
        }

        $this->data = $this->data->each->setAppends([])->pluck('count', 'moment');
        $this->data = $this->compileStatistics(
            $by,
            $this->request->get('from'),
            $this->request->get('to'),
            $this->data
        );

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $this->data->keys()->toArray(),
                'datasets' => [[
                    'label' => $label,
                    'borderColor' => 'rgba(108, 122, 135, 1)',
                    'backgroundColor' => 'rgba(108, 122, 135, 1)',
                    'data' => $this->data->values()->toArray(),
                    'order' => 1
                ]]
            ],
            'options' => [
                'maintainAspectRatio' => false,
                'spanGaps' => true,
                'legend' => [
                    'position' => 'right'
                ],
                'scales' => [
                    'y' => [
                        'stacked' => true,
                        'title' => [
                            'display' => true,
                            'text' => $label
                        ]
                    ],
                    'x' => [
                        'stacked' => true,
                    ]
                ],
                'interaction' => [
                    'mode' => 'nearest',
                    'axis' => 'x',
                    'intersect' => false
                ],
            ]
        ];
    }

    public function export()
    {
        $file = fopen('php://output', 'w');

        if ($this->data == null) {
            $this->getConfig();
        }

        $callback = function () use ($file) {
            foreach ($this->data as $key => $value) {
                fputcsv($file, [$key, $value]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=export.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }

    private function compileStatistics(string $by, $from, $to, $data): Collection
    {
        $stats = [];

        switch ($by) {
            case 'day':
                $period = collect(CarbonInterval::hour()->toPeriod(
                    $from ?? Carbon::now()->subDay()->format('Y-m-d H:i:s'),
                    $to ?? Carbon::now()->format('Y-m-d H:i:s')
                ))->map->format('Y-m-d H');
                break;

            case 'week':
                $period = collect(CarbonPeriod::create(
                    $from ?? Carbon::now()->subWeek(),
                    $to ?? Carbon::now()
                ))->map->format('Y-m-d');
                break;

            case 'month':
                $period = collect(
                    CarbonPeriod::create(
                        $from ?? Carbon::now()->subMonth(),
                        $to ?? Carbon::now()
                    )
                )->map->format('Y-m-d');
                break;

            case 'year':
                $period = collect(
                    CarbonPeriod::create(
                        $from ?? Carbon::now()->subYear(),
                        $to ?? Carbon::now()
                    )
                )->map->format('Y-m');
                break;
        }

        foreach ($period as $moment) {
            $stats[$moment] = $data[$moment] ?? 0;
        }

        return collect($stats);
    }
}
