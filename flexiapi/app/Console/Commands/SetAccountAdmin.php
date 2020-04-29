<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Account;
use App\Admin;

class SetAccountAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:set-admin {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give the admin role to an account';

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
        $account = Account::where('id', $this->argument('id'))->first();

        if (!$account) $this->error('Account not found, please use an existing ID');

        $admin = new Admin;
        $admin->account_id = $account->id;
        $admin->save();
    }
}
