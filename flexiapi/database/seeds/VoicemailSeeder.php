<?php

namespace Database\Seeders;

use App\Account;
use Database\Factories\AccountFileFactory;
use Illuminate\Database\Seeder;

class VoicemailSeeder extends Seeder
{
    public function run(): void
    {
        $account = Account::withoutGlobalScopes()
            ->first();

        if (! $account) {
            return;
        }

        AccountFileFactory::new()->for($account)->count(5)->create();

        $this->command->info("5 voicemails created for the account : $account->username.");
    }
}
