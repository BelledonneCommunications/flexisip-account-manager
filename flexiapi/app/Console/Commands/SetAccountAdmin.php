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

class SetAccountAdmin extends Command
{
    protected $signature = 'accounts:set-admin {id}';
    protected $description = 'Give the admin role to an account';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $account = Account::withoutGlobalScopes()->where('id', $this->argument('id'))->first();

        if (!$account) {
            $this->error('Account not found, please use an existing account id');
            return 1;
        }

        if ($account->admin) {
            $this->error('The account is already having the admin role');
            return 1;
        }

        $account->admin = true;
        $account->save();

        $this->info('Account '.$account->identifier.' is now admin');

        return 0;
    }
}
