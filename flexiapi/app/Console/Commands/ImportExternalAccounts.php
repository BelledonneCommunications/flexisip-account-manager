<?php

namespace App\Console\Commands;

use App\ExternalAccount;
use Illuminate\Console\Command;

class ImportExternalAccounts extends Command
{
    protected $signature = 'accounts:import-externals {file_path}';

    protected $description = 'Import external accounts from a file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (!file_exists($this->argument('file_path'))) {
            $this->error('The file does not exists');
            return 1;
        }

        $json = json_decode(file_get_contents($this->argument('file_path')));

        if (empty($json)) {
            $this->error('Nothing to import or incorrect file');
            return 1;
        }

        $existingUsernames = ExternalAccount::select('username')
            ->from('external_accounts')
            ->get()
            ->pluck('username');
        $existingCounter = 0;
        $importedCounter = 0;

        $externalAccounts = collect();
        foreach ($json as $account) {
            if ($existingUsernames->contains($account->username)) {
                $existingCounter++;
                continue;
            }

            $externalAccount = new ExternalAccount;
            $externalAccount->username = $account->username;
            $externalAccount->domain = $account->domain;
            $externalAccount->group = $account->group;
            $externalAccount->password = $account->password;
            $externalAccount->algorithm = $account->algorithm;

            $externalAccounts->push($externalAccount->toArray());
            $importedCounter++;
        }

        ExternalAccount::insert($externalAccounts->toArray());

        $this->info($importedCounter . ' accounts imported');

        if ($existingCounter > 0) {
            $this->info($existingCounter . ' accounts already in the database');
        }

        return 0;
    }
}
