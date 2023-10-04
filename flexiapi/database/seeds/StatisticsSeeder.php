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

namespace Database\Seeders;

use App\Account;
use App\StatisticsCall;
use App\StatisticsCallDevice;
use App\StatisticsMessage;
use App\StatisticsMessageDevice;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class StatisticsSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        StatisticsMessageDevice::truncate();
        StatisticsMessage::truncate();

        StatisticsCallDevice::truncate();
        StatisticsCall::truncate();
        Schema::enableForeignKeyConstraints();

        Account::factory(10)
            ->hasStatisticsFromMessages(20)
            ->hasStatisticsToMessageDevices(20)
            ->hasStatisticsFromCalls(20)
            ->hasStatisticsToCalls(20)
            ->create();
    }
}
