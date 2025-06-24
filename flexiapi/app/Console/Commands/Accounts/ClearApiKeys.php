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

namespace App\Console\Commands\Accounts;

use Illuminate\Console\Command;
use Carbon\Carbon;

use App\ApiKey;

class ClearApiKeys extends Command
{
    protected $signature = 'accounts:clear-api-keys {minutes?}';
    protected $description = 'Clear the expired user API Keys after n minutes and clear the other expired admin keys';

    public function handle()
    {
        // User API Keys
        $minutes = $this->argument('minutes') ?? config('app.api_key_expiration_minutes');

        if ($minutes == 0) {
            $this->info('Expiration time is set to 0, nothing to clear');
            return Command::SUCCESS;
        }

        $this->info('Deleting user API Keys unused after ' . $minutes . ' minutes');

        $count = ApiKey::whereNull('expires_after_last_used_minutes')
            ->where('last_used_at', '<', Carbon::now()->subMinutes($minutes)->toDateTimeString())
            ->delete();

        $this->info($count . ' user API Keys deleted');

        // Admin API Keys
        $keys = ApiKey::whereNotNull('expires_after_last_used_minutes')
            ->where('expires_after_last_used_minutes', '>', 0)
            ->with('account')
            ->get();

        $count = 0;

        foreach ($keys as $key) {
            if ($key->last_used_at->addMinutes($key->expires_after_last_used_minutes)->isPast()) {
                $this->info('Deleting ' . $key->account->identifier . ' admin API Key expired after ' . $key->expires_after_last_used_minutes .'min');
                $key->delete();
                $count++;
            }
        }

        $this->info($count . ' admin API Keys deleted');
    }
}
