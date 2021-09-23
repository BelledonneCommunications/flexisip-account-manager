<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Libraries\StatisticsCruncher;

class StatisticsController extends Controller
{
    public function showDay(Request $request)
    {
        $day = StatisticsCruncher::day();
        $maxDay = 0;
        foreach ($day as $hour) {
            if ($maxDay < $hour['all']) $maxDay = $hour['all'];
        }

        return view('admin.statistics.show_day', [
            'day' => $day,
            'max_day' => $maxDay,
        ]);
    }

    public function showWeek(Request $request)
    {
        $week = StatisticsCruncher::week();
        $maxWeek = 0;
        foreach ($week as $day) {
            if ($maxWeek < $day['all']) $maxWeek = $day['all'];
        }

        return view('admin.statistics.show_week', [
            'week' => $week,
            'max_week' => $maxWeek,
        ]);
    }

    public function showMonth(Request $request)
    {
        $month = StatisticsCruncher::month();
        $maxMonth = 0;
        foreach ($month as $day) {
            if ($maxMonth < $day['all']) $maxMonth = $day['all'];
        }

        return view('admin.statistics.show_month', [
            'month' => $month,
            'max_month' => $maxMonth,
        ]);
    }
}
