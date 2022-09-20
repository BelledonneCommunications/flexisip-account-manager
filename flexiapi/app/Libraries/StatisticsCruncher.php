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

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Carbon\CarbonInterval;

use App\Account;

class StatisticsCruncher
{
    public static function month()
    {
        $data = self::getAccountFrom(Carbon::now()->subMonth())
            ->get(array(
                DB::raw("date_format(creation_time,'%Y-%m-%d') as moment"),
                DB::raw('COUNT(*) as "count"')
            ))->each->setAppends([])->pluck('count', 'moment');

        $dataAliases = self::getAccountFrom(Carbon::now()->subMonth())
            ->whereIn('id', function ($query) {
                $query->select('account_id')
                      ->from('aliases');
            })
            ->get(array(
                DB::raw("date_format(creation_time,'%Y-%m-%d') as moment"),
                DB::raw('COUNT(*) as "count"')
            ))->each->setAppends([])->pluck('count', 'moment');

        $dataActivated = self::getAccountFrom(Carbon::now()->subMonth())
            ->where('activated', true)
            ->get(array(
                DB::raw("date_format(creation_time,'%Y-%m-%d') as moment"),
                DB::raw('COUNT(*) as "count"')
            ))->each->setAppends([])->pluck('count', 'moment');

        $dataAliasesActivated = self::getAccountFrom(Carbon::now()->subMonth())
            ->where('activated', true)
            ->whereIn('id', function ($query) {
                $query->select('account_id')
                      ->from('aliases');
            })
            ->get(array(
                DB::raw("date_format(creation_time,'%Y-%m-%d') as moment"),
                DB::raw('COUNT(*) as "count"')
            ))->each->setAppends([])->pluck('count', 'moment');

        return self::compileStatistics(
            collect(CarbonPeriod::create(Carbon::now()->subMonth(), Carbon::now()))->map->format('Y-m-d'),
            $data,
            $dataAliases,
            $dataActivated,
            $dataAliasesActivated
        );
    }

    public static function week()
    {
        $data = self::getAccountFrom(Carbon::now()->subWeek())
            ->get(array(
                DB::raw("date_format(creation_time,'%Y-%m-%d') as moment"),
                DB::raw('COUNT(*) as "count"')
            ))->each->setAppends([])->pluck('count', 'moment');

        $dataAliases = self::getAccountFrom(Carbon::now()->subWeek())
            ->whereIn('id', function ($query) {
                $query->select('account_id')
                      ->from('aliases');
            })
            ->get(array(
                DB::raw("date_format(creation_time,'%Y-%m-%d') as moment"),
                DB::raw('COUNT(*) as "count"')
            ))->each->setAppends([])->pluck('count', 'moment');

        $dataActivated = self::getAccountFrom(Carbon::now()->subWeek())
            ->where('activated', true)
            ->get(array(
                DB::raw("date_format(creation_time,'%Y-%m-%d') as moment"),
                DB::raw('COUNT(*) as "count"')
            ))->each->setAppends([])->pluck('count', 'moment');

        $dataAliasesActivated = self::getAccountFrom(Carbon::now()->subWeek())
            ->where('activated', true)
            ->whereIn('id', function ($query) {
                $query->select('account_id')
                      ->from('aliases');
            })
            ->get(array(
                DB::raw("date_format(creation_time,'%Y-%m-%d') as moment"),
                DB::raw('COUNT(*) as "count"')
            ))->each->setAppends([])->pluck('count', 'moment');

        return self::compileStatistics(
            collect(CarbonPeriod::create(Carbon::now()->subWeek(), Carbon::now()))->map->format('Y-m-d'),
            $data,
            $dataAliases,
            $dataActivated,
            $dataAliasesActivated
        );
    }

    public static function day()
    {
        $data = self::getAccountFrom(Carbon::now()->subDay())
            ->get(array(
                DB::raw("date_format(creation_time,'%Y-%m-%d %H') as moment"),
                DB::raw('COUNT(*) as "count"')
            ))->each->setAppends([])->pluck('count', 'moment');

        $dataAliases = self::getAccountFrom(Carbon::now()->subDay())
            ->whereIn('id', function ($query) {
                $query->select('account_id')
                      ->from('aliases');
            })
            ->get(array(
                DB::raw("date_format(creation_time,'%Y-%m-%d %H') as moment"),
                DB::raw('COUNT(*) as "count"')
            ))->each->setAppends([])->pluck('count', 'moment');

        $dataActivated = self::getAccountFrom(Carbon::now()->subDay())
            ->where('activated', true)
            ->get(array(
                DB::raw("date_format(creation_time,'%Y-%m-%d %H') as moment"),
                DB::raw('COUNT(*) as "count"')
            ))->each->setAppends([])->pluck('count', 'moment');

        $dataAliasesActivated = self::getAccountFrom(Carbon::now()->subDay())
            ->where('activated', true)
            ->whereIn('id', function ($query) {
                $query->select('account_id')
                      ->from('aliases');
            })
            ->get(array(
                DB::raw("date_format(creation_time,'%Y-%m-%d %H') as moment"),
                DB::raw('COUNT(*) as "count"')
            ))->each->setAppends([])->pluck('count', 'moment');

        return self::compileStatistics(
            collect(CarbonInterval::hour()->toPeriod(Carbon::now()->subDay(), Carbon::now()))->map->format('Y-m-d H'),
            $data,
            $dataAliases,
            $dataActivated,
            $dataAliasesActivated
        );
    }

    private static function getAccountFrom($date)
    {
        return Account::where('creation_time', '>=', $date)
            ->groupBy('moment')
            ->orderBy('moment', 'DESC')
            ->setEagerLoads([]);
    }

    private static function compileStatistics($period, $data, $dataAliases, $dataActivated, $dataAliasesActivated)
    {
        $stats = [];

        foreach ($period as $moment) {
            $all = $data[$moment] ?? 0;
            $aliases = $dataAliases[$moment] ?? 0;
            $activated = $dataActivated[$moment] ?? 0;
            $activatedAliases = $dataAliasesActivated[$moment] ?? 0;

            $stats[$moment] = [
                'all' => $all,
                'phone' => $aliases,
                'email' => $all - $aliases,
                'activated_phone' => $activatedAliases,
                'activated_email' => $activated - $activatedAliases
            ];
        }

        return $stats;
    }
}
