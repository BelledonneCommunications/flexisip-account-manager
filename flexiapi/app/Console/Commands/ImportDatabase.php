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

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Account;
use App\Admin;
use App\Alias;
use App\ApiKey;
use App\DigestNonce;
use App\EmailChanged;
use App\Password;
use App\PhoneChangeCode;

class ImportDatabase extends Command
{
    protected $signature = 'db:import {dbname} {sqlite-file-path?} {--u|username=} {--p|password=} {--P|port=3306} {--t|type=mysql} {--host=localhost} {--accounts-table=accounts} {--aliases-table=aliases} {--passwords-table=passwords}';
    protected $description = 'Import an existing Flexisip database into FlexiAPI';
    private $pagination = 1000;

    public function __construct()
    {
        parent::__construct();
    }

    public function enableForeignKeyCheck()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function disableForeignKeyCheck()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    }

    public function handle()
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'    => $this->option('type'),
            'host'      => $this->option('host'),
            'database'  => $this->argument('dbname'),
            'username'  => $this->option('username'),
            'password'  => $this->option('password'),
            'port'  => $this->option('port'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ], 'default');

        if (!$this->argument('sqlite-file-path')) {
            $this->confirm('No SQLite database file was specified : Do you wish to continue?');
        } else {
            $capsule->addConnection([
                'driver'    => 'sqlite',
                'database'  => $this->argument('sqlite-file-path'),
            ], 'sqlite');
        }

        $capsule->setAsGlobal();

        // Ensure that the target database is empty
        if (Account::count() > 0) {
            $this->error('An empty database is required to run the migration');
            return 1;
        }

        $accountsCount = Capsule::table($this->option('accounts-table'))->count();

        if ($this->confirm($accountsCount . ' accounts will be migrated : Do you wish to continue?')) {
            // Accounts
            $this->info('Migrating the accounts');

            $pages = $accountsCount / $this->pagination;
            $bar = $this->output->createProgressBar($pages);

            for ($page = 0; $page <= $pages; $page++) {
                $originAccounts = Capsule::table($this->option('accounts-table'))
                                         ->take($this->pagination)
                                         ->skip($page*$this->pagination)
                                         ->get()
                                         ->map(function ($element) {
                                            // Fix bad creation_time
                                            $creationTime = strtotime($element->creation_time);
                                            if ($creationTime == false || $creationTime < 0) {
                                                $element->creation_time = gmdate('Y-m-d H:i:s', 1);
                                            }
                                            return (array)$element;
                                         })
                                         ->toArray();

                Account::insert($originAccounts);

                $bar->advance();
            }

            $bar->finish();

            $this->newLine();

            $this->disableForeignKeyCheck();

            // Passwords
            $this->info('Migrating the passwords');

            $pages = Capsule::table($this->option('passwords-table'))->count() / $this->pagination;
            $bar = $this->output->createProgressBar($pages);

            for ($page = 0; $page <= $pages; $page++) {
                $originPasswords = Capsule::table($this->option('passwords-table'))
                                          ->take($this->pagination)
                                          ->skip($page*$this->pagination)
                                          ->get()
                                          ->map(function ($element) {
                                            return (array)$element;
                                          })
                                          ->toArray();

                Password::insert($originPasswords);

                $bar->advance();
            }

            $bar->finish();

            $this->newLine();

            // Aliases
            $this->info('Migrating the aliases');

            $pages = Capsule::table($this->option('aliases-table'))->count() / $this->pagination;
            $bar = $this->output->createProgressBar($pages);

            for ($page = 0; $page <= $pages; $page++) {
                $originAliases = Capsule::table($this->option('aliases-table'))
                                          ->take($this->pagination)
                                          ->skip($page*$this->pagination)
                                          ->get()
                                          ->map(function ($element) {
                                            return (array)$element;
                                          })
                                          ->toArray();

                Alias::insert($originAliases);

                $bar->advance();
            }

            $bar->finish();

            // SQLite database migration

            if ($this->argument('sqlite-file-path')) {
                $this->newLine();

                $this->info('Migrating the admins');

                $originAdmins = Capsule::connection('sqlite')
                                            ->table('admins')
                                            ->get()
                                            ->map(function ($element) {
                                                return (array)$element;
                                            })
                                            ->toArray();
                Admin::insert($originAdmins);

                $this->info('Migrating the api keys');

                $originApiKeys = Capsule::connection('sqlite')
                                            ->table('api_keys')
                                            ->get()
                                            ->map(function ($element) {
                                                return (array)$element;
                                            })
                                            ->toArray();
                ApiKey::insert($originApiKeys);

                $this->info('Migrating the nonces');

                $originNonces = Capsule::connection('sqlite')
                                            ->table('nonces')
                                            ->get()
                                            ->map(function ($element) {
                                                return (array)$element;
                                            })
                                            ->toArray();
                DigestNonce::insert($originNonces);

                $this->info('Migrating the email changed');

                $originEmailChanged = Capsule::connection('sqlite')
                                            ->table('email_changed')
                                            ->get()
                                            ->map(function ($element) {
                                                return (array)$element;
                                            })
                                            ->toArray();
                EmailChanged::insert($originEmailChanged);

                $this->info('Migrating the phone change code');

                $originPhoneChangeCodes = Capsule::connection('sqlite')
                                            ->table('phone_change_codes')
                                            ->get()
                                            ->map(function ($element) {
                                                return (array)$element;
                                            })
                                            ->toArray();
                PhoneChangeCode::insert($originPhoneChangeCodes);
            }

            $this->enableForeignKeyCheck();

            $this->newLine();
            $this->info('Databases migrated');
        }

        return 0;
    }
}
