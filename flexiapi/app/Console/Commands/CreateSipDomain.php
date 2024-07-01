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

use App\SipDomain;
use Illuminate\Console\Command;

class CreateSipDomain extends Command
{
    protected $signature = 'sip_domains:create-update {domain} {--super}';
    protected $description = 'Create a SIP Domain';

    public function handle()
    {
        $this->info('Your will create or update a SIP Domain in the database');

        $sipDomain = SipDomain::where('domain', $this->argument('domain'))->firstOrNew();
        $sipDomain->domain = $this->argument('domain');

        $sipDomain->exists
            ? $this->info('The domain already exists, updating it')
            : $this->info('A new domain will be created');

        $sipDomain->super = (bool)$this->option('super');
        $sipDomain->super
            ? $this->info('Set as a super domain')
            : $this->info('Set as a normal domain');

        $sipDomain->save();

        return Command::SUCCESS;
    }
}
