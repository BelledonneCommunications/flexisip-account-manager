<?php

namespace App\Console\Commands;

use App\Account;
use App\AccountTombstone;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClearOldAccountsTombstones extends Command
{
    protected $signature = 'accounts:clear-accounts-tombstones {days} {--apply}';
    protected $description = 'Clear deleted accounts tombstones after n days';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $tombstones = AccountTombstone::where('created_at', '<',
            Carbon::now()->subDays($this->argument('days'))->toDateTimeString()
        );

        if ($this->option('apply')) {
            $this->info($tombstones->count() . ' tombstones deleted');
            $tombstones->delete();
        } else {
            $this->info($tombstones->count() . ' tombstones to delete');
        }
    }
}
