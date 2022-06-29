<?php

namespace App\Console\Commands;

use App\Account;
use Illuminate\Console\Command;

class ExportToExternalAccounts extends Command
{
    protected $signature = 'accounts:export-to-externals {group} {--o|output=}';

    protected $description = 'Export accounts from a group as external ones';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $accounts = Account::where('group', $this->argument('group'))
                           ->with('passwords')
                           ->get();

        if ($accounts->count() == 0) {
            $this->error('Nothing to export');
            return;
        }

        $this->info('Exporting '.$accounts->count().' accounts');

        $data = [];

        foreach ($accounts as $account) {
            array_push($data, [
                'username' => $account->username,
                'domain' => $account->domain,
                'group' => $account->group,
                'password' => $account->passwords->first()->password,
                'algorithm' => $account->passwords->first()->algorithm,
            ]);
        }

        file_put_contents(
            $this->option('output') ?? getcwd() . '/exported_accounts.json',
            json_encode($data)
        );

        $this->info('Exported');
    }
}
