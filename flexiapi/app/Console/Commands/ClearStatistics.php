<?php

namespace App\Console\Commands;

use App\StatisticsCall;
use App\StatisticsMessage;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClearStatistics extends Command
{
    protected $signature = 'app:clear-statistics {days} {--apply}';
    protected $description = 'Command description';

    public function handle()
    {
        $calls = StatisticsCall::where(
            'created_at',
            '<',
            Carbon::now()->subDays($this->argument('days'))->toDateTimeString()
        );
        $messages = StatisticsMessage::where(
            'created_at',
            '<',
            Carbon::now()->subDays($this->argument('days'))->toDateTimeString()
        );

        $callsCount = $calls->count();
        $messagesCount = $messages->count();

        if ($this->option('apply')) {
            $this->info($callsCount . ' calls statistics in deletion…');
            $calls->delete();
            $this->info($callsCount . ' calls statistics deleted');

            $this->info($messagesCount . ' messages statistics in deletion…');
            $messages->delete();
            $this->info($messagesCount . ' messages statistics deleted');

            return Command::SUCCESS;
        }

        $this->info($callsCount . ' calls statistics to delete');
        $this->info($messagesCount . ' messages statistics to delete');
        return Command::SUCCESS;
    }
}
