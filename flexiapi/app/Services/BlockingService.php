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

namespace App\Services;

use App\Account;

use Carbon\Carbon;

class BlockingService
{
    public function __construct(public Account $account)
    {
    }

    public function checkBlock(): bool
    {
        if ($this->account->blocked) {
            return true;
        }

        $isBlockable = $this->isBlockable();

        if ($isBlockable) {
            $this->account->blocked = true;
            $this->account->save();
        }

        return $isBlockable;
    }

    public function isBlockable(): bool
    {
        if (config('app.blocking_amount_events_authorized_during_period') == 0) {
            return false;
        }

        return $this->countEvents() >= config('app.blocking_amount_events_authorized_during_period');
    }

    private function countEvents(): int
    {
        $events = 0;

        $events += $this->account->recoveryCodes()->where(
            'created_at',
            '>',
            Carbon::now()->subMinutes(config('app.blocking_time_period_check'))->toDateTimeString()
        )->count();

        $events += $this->account->phoneChangeCodes()->where(
            'created_at',
            '>',
            Carbon::now()->subMinutes(config('app.blocking_time_period_check'))->toDateTimeString()
        )->count();

        $events += $this->account->emailChangeCodes()->where(
            'created_at',
            '>',
            Carbon::now()->subMinutes(config('app.blocking_time_period_check'))->toDateTimeString()
        )->count();

        // Deprecated, also detect if the account itself was updated recently, might be because of the confirmation_key change
        if (Carbon::now()->subMinutes(config('app.blocking_time_period_check'))->isBefore($this->account->updated_at)) {
            $events++;
        }

        return $events;
    }
}
