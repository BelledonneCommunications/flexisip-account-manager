<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Account;
use App\Admin;
use App\ApiKey;
use Carbon\Carbon;

class CreateAdminAccountTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:create-admin-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test admin account, only for tests purpose';

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
     * @return int
     */
    public function handle()
    {
        $username = 'admin_test';
        $domain = 'sip.example.org';
        $secret = 'no_secret_at_all';

        // Delete the existing keys
        ApiKey::where('key', $secret)->delete();

        // Delete the account if it already exists
        $account = Account::withoutGlobalScopes()
                          ->where('username', $username)
                          ->where('domain', $domain)
                          ->first();

        if ($account) {
            // We don't have foreign keys yet…
            $account->admin()->delete();
            $account->delete();
        }

        $account = new Account;
        $account->username = $username;
        $account->domain = $domain;
        $account->email = 'admin_test@sip.example.org';
        $account->activated = true;
        $account->user_agent = 'Test';
        $account->ip_address = '0.0.0.0';
        $account->creation_time = Carbon::now();
        $account->save();

        $admin = new Admin;
        $admin->account_id = $account->id;
        $admin->save();

        $apiKey = new ApiKey;
        $apiKey->account_id = $account->id;
        $apiKey->key = $secret;
        $apiKey->save();

        $this->info('Admin test account created: "sip:' . $username . '@' . $domain . '" | API Key: "' . $secret . '"');

        return 0;
    }
}
