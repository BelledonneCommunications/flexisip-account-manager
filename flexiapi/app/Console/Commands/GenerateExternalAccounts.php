<?php

namespace App\Console\Commands;

use App\Account;
use App\Password;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateExternalAccounts extends Command
{
    protected $signature = 'accounts:generate-external {amount} {group}';

    protected $description = 'Generate external accounts in the designed group';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $accounts = collect();
        $passwords = collect();
        $algorithm = 'SHA-256';

        $i = 0;

        while ($i < $this->argument('amount')) {
            $account = new Account;
            $account->username = $this->argument('group') . '_' . Str::random(6);
            $account->domain = config('app.sip_domain');
            $account->activated = 1;
            $account->ip_address = '127.0.0.1';
            $account->user_agent = 'External Account Generator';
            $account->group = $this->argument('group');
            $account->creation_time = Carbon::now();
            $i++;

            $account->push($account->toArray());
        }

        Account::insert($accounts->toArray());

        $insertedAccounts = Account::where('group', $this->argument('group'))
            ->orderBy('creation_time', 'desc')
            ->take($this->argument('amount'))
            ->get();

        foreach ($insertedAccounts as $account) {
            $password = new Password;
            $password->account_id = $account->id;
            $password->password = bchash($account->username, $account->resolvedRealm, Str::random(6), $algorithm);
            $password->algorithm = $algorithm;
            $passwords->push($password->only(['account_id', 'password', 'algorithm']));
        }

        Password::insert($passwords->toArray());

        $this->info($this->argument('amount') . ' accounts created under the "' . $this->argument('group') . '" group');

        return 0;
    }
}
