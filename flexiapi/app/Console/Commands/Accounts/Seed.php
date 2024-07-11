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

use Database\Seeders\LiblinphoneTesterAccoutSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class Seed extends Command
{
    protected $signature = 'accounts:seed {json-file-path}';
    protected $description = 'Seed some accounts from a JSON file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $file = $this->argument('json-file-path');

        if (!file_exists($file)) {
            $this->info('The JSON file doesn\'t exists');
            return Command::FAILURE;
        }

        $json = json_decode(file_get_contents($file));

        if ($json == null || $json == false) {
            $this->info('Malformed JSON file');
            return Command::FAILURE;
        }

        $seeder = App::make(LiblinphoneTesterAccoutSeeder::class);
        $seeder->run($json);

        return Command::SUCCESS;
    }
}
