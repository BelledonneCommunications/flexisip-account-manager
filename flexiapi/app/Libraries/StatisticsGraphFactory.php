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
use App\Enums\StatisticsGraphDirection;
use App\Enums\StatisticsGraphType;
use App\StatisticsCall;
use App\StatisticsMessage;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsGraphFactory
{
    private $data = null;

    public function __construct(private Request $request)
    {
    }

    public function getSpaceGraphConfig(StatisticsGraphType $type, ?string $domain)
    {
        return match($type) {
            StatisticsGraphType::MESSAGES => $this->makeGraph($this->makeSpaceEventsQuery(StatisticsMessage::query(), $domain), $type),
            StatisticsGraphType::CALLS => $this->makeGraph($this->makeSpaceEventsQuery(StatisticsCall::query(), $domain), $type),
            StatisticsGraphType::ACCOUNTS => $this->makeGraph($this->makeSpaceAccountsQuery($domain), $type),
        };
    }

    public function getAccountGraphConfig(StatisticsGraphType $type, string $username, string $domain, StatisticsGraphDirection $direction)
    {
        return match($type) {
            StatisticsGraphType::MESSAGES => $this->makeGraph(StatisticsMessage::query()->{$direction->scope('UsernameDomain')}($username, $domain), $type),
            StatisticsGraphType::CALLS => $this->makeGraph(StatisticsCall::query()->{$direction->scope('UsernameDomain')}($username, $domain), $type),
        };
    }

    public function export()
    {
        $file = fopen('php://output', 'w');

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

    private function makeSpaceEventsQuery(Builder $query, ?string $domain): Builder
    {
        $domain = Auth::user()?->superAdmin ? $domain : space()->domain;

        if ($domain) {
            $query = $query->involvingDomain($domain);
        }

        if ($this->request->has('contacts_list')) {
            $contactsListId = $this->request->input('contacts_list');
            $query = $query->where(function (Builder $query) use ($contactsListId) {
                $query->where(function (Builder $query) use ($contactsListId) {
                    $query->fromByContactsList($contactsListId);
                })->orWhere(function (Builder $query) use ($contactsListId) {
                    $query->toByContactsList($contactsListId);
                });
            });
        }

        return $query;
    }

    private function makeSpaceAccountsQuery(?string $domain): Builder
    {
        $domain = Auth::user()?->superAdmin ? $domain : space()->domain;

        $query = Account::query();

        if ($domain) {
            $query->where('domain', $domain);
        }

        if ($this->request->has('contacts_list')) {
            $query->whereIn('id', function ($query) {
                $query->select('contact_id')
                    ->from('contacts_list_contact')
                    ->where('contacts_list_id', $this->request->input('contacts_list'));
            });
        }

        return $query;
    }

    private function makeGraph(Builder $query, StatisticsGraphType $type)
    {
        $label = ucfirst($type->value);
        $dateColumn = match($type) {
            StatisticsGraphType::MESSAGES => 'sent_at',
            StatisticsGraphType::CALLS => 'initiated_at',
            StatisticsGraphType::ACCOUNTS => 'created_at',
        };

        $this->data = $query->orderBy($dateColumn);
        $this->data = $this->data->groupBy('moment')
            ->orderBy('moment', 'desc')
            ->setEagerLoads([]);

        if ($this->request->input('to')) {
            $this->data = $this->data->where($dateColumn, '<=', $this->request->input('to'));
        }

        $by = $this->request->input('by', 'day');

        $this->data = match ($by) {
            'day' => $this->data->where($dateColumn, '>=', $this->request->input('from', Carbon::now()->subDay()->format('Y-m-d H:i:s')))
                ->get([
                    DB::raw("date_format(" . $dateColumn . ",'%Y-%m-%d %H') as moment"),
                    DB::raw('COUNT(*) as "count"')
                ]),
            'week' => $this->data->where($dateColumn, '>=', $this->request->input('from', Carbon::now()->subWeek()->format('Y-m-d H:i:s')))
                ->get([
                    DB::raw("date_format(" . $dateColumn . ",'%Y-%m-%d') as moment"),
                    DB::raw('COUNT(*) as "count"')
                ]),
            'month' => $this->data->where($dateColumn, '>=', $this->request->input('from', Carbon::now()->subMonth()->format('Y-m-d H:i:s')))
                ->get([
                    DB::raw("date_format(" . $dateColumn . ",'%Y-%m-%d') as moment"),
                    DB::raw('COUNT(*) as "count"')
                ]),
            'year' => $this->data->where($dateColumn, '>=', $this->request->input('from', Carbon::now()->subYear()->format('Y-m-d H:i:s')))
                ->get([
                    DB::raw("date_format(" . $dateColumn . ",'%Y-%m') as moment"),
                    DB::raw('COUNT(*) as "count"')
                ]),
            default => $this->data,
        };

        $this->data = $this->data->each->setAppends([])->pluck('count', 'moment');
        $this->data = $this->compileStatistics(
            $by,
            $this->request->input('from'),
            $this->request->input('to'),
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

    private function compileStatistics(string $by, $from, $to, $data): Collection
    {
        $stats = [];

        $period = match ($by) {
            'day' => collect(CarbonInterval::hour()->toPeriod(
                $from ?? Carbon::now()->subDay()->format('Y-m-d H:i:s'),
                $to ?? Carbon::now()->format('Y-m-d H:i:s')
            ))->map->format('Y-m-d H'),

            'week' => collect(CarbonPeriod::create(
                $from ?? Carbon::now()->subWeek(),
                $to ?? Carbon::now()
            ))->map->format('Y-m-d'),

            'month' => collect(CarbonPeriod::create(
                $from ?? Carbon::now()->subMonth(),
                $to ?? Carbon::now()
            ))->map->format('Y-m-d'),

            'year' => collect(CarbonPeriod::create(
                $from ?? Carbon::now()->subYear(),
                $to ?? Carbon::now()
            ))->map->format('Y-m'),
        };

        foreach ($period as $moment) {
            $stats[$moment] = $data[$moment] ?? 0;
        }

        return collect($stats);
    }
}
