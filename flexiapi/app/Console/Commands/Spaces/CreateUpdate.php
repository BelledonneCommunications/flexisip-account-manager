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

use App\PasswordAlgorithm;
use App\Space;
use App\SpaceDigestAuthenticationConfiguration;
use Illuminate\Console\Command;

class CreateUpdate extends Command
{
    protected $signature = 'spaces:create-update {sip_domain} {host} {name} {--super} {--digest-realm=}';
    protected $description = 'Create a Space';

    public function handle()
    {
        $this->info('Your are creating or updating a Space in the database');

        if (empty(config('app.root_host'))) {
            $this->error('The environnement variable APP_ROOT_HOST doesn\'t seems to be set');
            return Command::FAILURE;
        }

        if (!str_ends_with($this->argument('host'), config('app.root_host'))) {
            $this->error('The provided host doesn\'t seems to ends with ' . config('app.root_host'));
            return Command::FAILURE;
        }

        if ($spaceName = Space::where('name', $this->argument('name'))->first()) {
            if ($spaceName->domain != $this->argument('sip_domain')) {
                $this->error('A Space already exists with the same name in the system');
                return Command::FAILURE;
            }
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

        $space->super = (bool) $this->option('super');
        $space->super
            ? $this->info('Set as a super Space')
            : $this->info('Set as a normal Space');

        $space->save();
        $space->refresh();

        if (!$space->digestAuthenticationConfiguration) {
            $realm = $this->option('digest-realm');

            $digestMessage = empty($realm)
                ? "This new Space doesn't have a Digest authentication configuration, do you want to create it?"
                : 'A Digest authentication configuration will be set with the realm "' . $realm . '", is it ok?';

            if ($this->confirm($digestMessage, true)) {
                if (empty($realm)) {
                    $realm = $this->ask('Which realm do you want to use for the authentication? (default to the SIP domain)', $this->argument('sip_domain'));
                }

                $digestAuthenticationConfiguration = new SpaceDigestAuthenticationConfiguration([
                    'realm' => $realm,
                    'default_password_algorithm' => PasswordAlgorithm::SHA256,
                    'space_id' => $space->id,
                ]);
                $digestAuthenticationConfiguration->save();

                $this->info('Digest configuration set');
            }
        }

        return Command::SUCCESS;
    }
}
