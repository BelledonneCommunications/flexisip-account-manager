<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2020 Belledonne Communications SARL, All rights reserved.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Account;
use App\ApiKey;
use Carbon\Carbon;

class CreateAdminAccountTest extends Command
{
    protected $signature = 'accounts:create-admin-test';
    protected $description = 'Create a test admin account, only for tests purpose';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $username = 'admin_test';
        $domain = 'sip.example.org';

        $this->call('accounts:create-admin-account', [
            '--username' => $username,
            '--domain' => $domain,
            '--password' => 'admin_test_password'
        ]);

        $secret = 'no_secret_at_all';

        $account = Account::withoutGlobalScopes()
            ->where('username', $username)
            ->where('domain', $domain)
            ->first();

        ApiKey::where('account_id', $account->id)->delete();

        $apiKey = new ApiKey;
        $apiKey->account_id = $account->id;
        $apiKey->last_used_at = Carbon::now();
        $apiKey->key = $secret;
        $apiKey->save();

        $this->info('API Key updated to: ' . $secret);

        return 0;
    }
}
