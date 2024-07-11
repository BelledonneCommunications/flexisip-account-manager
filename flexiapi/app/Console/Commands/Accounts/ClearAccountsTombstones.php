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

use App\AccountTombstone;

class ClearAccountsTombstones extends Command
{
    protected $signature = 'accounts:clear-accounts-tombstones {days} {--apply}';
    protected $description = 'Clear deleted accounts tombstones after n days';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $tombstones = AccountTombstone::where(
            'created_at',
            '<',
            Carbon::now()->subDays($this->argument('days'))->toDateTimeString()
        );

        if ($this->option('apply')) {
            $this->info($tombstones->count() . ' tombstones deleted');
            $tombstones->delete();

            return 0;
        }

        $this->info($tombstones->count() . ' tombstones to delete');
        return 0;
    }
}
