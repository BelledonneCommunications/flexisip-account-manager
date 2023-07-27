<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\StatisticsMessage;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
            'type' => $request->get('type'),
            'to' => $request->get('to'),
            'by' => $request->get('by'),
        ]);
    }

    public function show(Request $request, string $type = 'messages')
    {
        $request->validate([
            'from' => 'date_format:Y-m-d|before:to',
            'to' => 'date_format:Y-m-d|after:from',
            'by' => 'in:day,week,month,year',
        ]);

        $dateColumn = 'created_at';
        $label = 'Label';

        switch ($type) {
            case 'messages':
                $dateColumn = 'sent_at';
                $label = 'Messages';
                $data = StatisticsMessage::orderBy($dateColumn, 'asc');
                break;

            case 'accounts':
                $label = 'Accounts';
                $data = Account::orderBy($dateColumn, 'asc');
                break;
        }

        $data = $data->groupBy('moment')
            ->orderBy('moment', 'desc')
            ->setEagerLoads([]);

        if ($request->get('to')) {
            $data = $data->where($dateColumn, '<=', $request->get('to'));
        }

        $by = $request->get('by', 'day');

        switch ($by) {
            case 'day':
                $data = $data->where($dateColumn, '>=', $request->get('from', Carbon::now()->subDay()->format('Y-m-d H:i:s')))
                    ->get([
                        DB::raw("date_format(" . $dateColumn . ",'%Y-%m-%d %H') as moment"),
                        DB::raw('COUNT(*) as "count"')
                    ]);
                break;
            case 'week':
                $data = $data->where($dateColumn, '>=', $request->get('from', Carbon::now()->subWeek()->format('Y-m-d H:i:s')))
                    ->get([
                        DB::raw("date_format(" . $dateColumn . ",'%Y-%m-%d') as moment"),
                        DB::raw('COUNT(*) as "count"')
                    ]);
                break;
            case 'month':
                $data = $data->where($dateColumn, '>=', $request->get('from', Carbon::now()->subMonth()->format('Y-m-d H:i:s')))
                    ->get([
                        DB::raw("date_format(" . $dateColumn . ",'%Y-%m-%d') as moment"),
                        DB::raw('COUNT(*) as "count"')
                    ]);
                break;
            case 'year':
                $data = $data->where($dateColumn, '>=', $request->get('from', Carbon::now()->subYear()->format('Y-m-d H:i:s')))
                    ->get([
                        DB::raw("date_format(" . $dateColumn . ",'%Y-%m') as moment"),
                        DB::raw('COUNT(*) as "count"')
                    ]);
                break;
        }

        $data = $data->each->setAppends([])->pluck('count', 'moment');

        $data = $this->compileStatistics(
            $by,
            $request->get('from'),
            $request->get('to'),
            $data
        );

        if ($request->get('export', false)) {
            $file = fopen('php://output', 'w');

            $callback = function () use ($data, $file) {
                foreach ($data as $key => $value) {
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

        $config = [
            'type' => 'bar',
            'data' => [
                'labels' => $data->keys()->toArray(),
                'datasets' => [[
                    'label' => $label,
                    'borderColor' => 'rgba(108, 122, 135, 1)',
                    'backgroundColor' => 'rgba(108, 122, 135, 1)',
                    'data' => $data->values()->toArray(),
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

        return view('admin.statistics.show', [
            'jsonConfig' => json_encode($config),
            'by' => $by,
            'type' => $type,
            'request' => $request
        ]);
    }

    private static function compileStatistics(string $by, $from, $to, $data): Collection
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
