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
    protected $description = 'Clear the expired API Keys after n minutes';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $minutes = $this->argument('minutes') ?? config('app.api_key_expiration_minutes');

        if ($minutes == 0) {
            $this->info('Expiration time is set to 0, nothing to clear');
            return 0;
        }

        $this->info('Deleting api keys unused after ' . $minutes . ' minutes');

        $count = ApiKey::where(
            'last_used_at',
            '<',
            Carbon::now()->subMinutes($minutes)->toDateTimeString()
        )->delete();

        $this->info($count . ' api keys deleted');
    }
}
