<?php

namespace App\Console\Commands;

use App\Account;
use App\Password;
use App\Rules\NoUppercase;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
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
        $validator = Validator::make([
            'amount' => $this->argument('amount'),
            'group' => $this->argument('group'),
        ], [
            'amount' => ['required', 'integer'],
            'group' => ['required', 'alpha-dash', new NoUppercase]
        ]);

        if ($validator->fails()) {
            $this->info('External accounts no created:');

            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return 1;
        }

        $groups = Account::distinct('group')
                         ->whereNotNull('group')
                         ->get('group')
                         ->pluck('group')
                         ->toArray();

        if (!in_array($this->argument('group'), $groups)) {
            $this->info('Existing groups: '.implode(',', $groups));

            if (!$this->confirm('You are creating a new group of External Account, are you sure?', false))
            {
                $this->info('Creation aborted');
                return 0;
            }
        }

        $accounts = collect();
        $passwords = collect();
        $algorithm = 'SHA-256';

        $i = 0;

        while ($i < $this->argument('amount')) {
            $account = new Account;
            $account->username = Str::random(12);
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
