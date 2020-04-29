<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use App\Account;

class RemoveUnconfirmedAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:clear-unconfirmed {days} {--apply}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear unconfirmed accounts after n days';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $accounts = Account::where('activated', false)->where('creation_time', '<',
            Carbon::now()->subDays($this->argument('days'))->toDateTimeString()
        )->get();

        if ($this->option('apply')) {
            $this->info($accounts->count() . ' accounts deleted');
            $accounts->delete();
        } else {
            $this->info($accounts->count() . ' accounts to delete');
        }
    }
}
