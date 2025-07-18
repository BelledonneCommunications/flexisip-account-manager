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

namespace App\Console\Commands\Spaces;

use App\Space;
use Illuminate\Console\Command;

class CreateUpdate extends Command
{
    protected $signature = 'spaces:create-update {sip_domain} {host} {name} {--super}';
    protected $description = 'Create a Space';

    public function handle()
    {
        $this->info('Your are creating or updating a Space in the database');

        if (empty(config('app.root_host'))) {
            $this->error('The environnement variable APP_ROOT_HOST doesn\'t seems to be set');
        }

        if (!str_ends_with($this->argument('host'), config('app.root_host'))) {
            $this->error('The provided host doesn\'t seems to ends with ' . config('app.root_host'));
        }

        $space = Space::where('domain', $this->argument('sip_domain'))->firstOrNew();
        $space->host = $this->argument('host');
        $space->domain = $this->argument('sip_domain');
        $space->name = $this->argument('name');

        if ($hostSpace = Space::where('host', $this->argument('host'))->first()) {
            if (!$space->exists && $hostSpace->domain != $space->domain) {
                $this->error('A Space with this host and a different sip_domain already exists in the database');
                return Command::FAILURE;
            }
        }

        $space->exists
            ? $this->info('The space already exists, updating it')
            : $this->info('A new Space will be created');

        $space->super = (bool)$this->option('super');
        $space->super
            ? $this->info('Set as a super Space')
            : $this->info('Set as a normal Space');

        $space->save();

        return Command::SUCCESS;
    }
}
