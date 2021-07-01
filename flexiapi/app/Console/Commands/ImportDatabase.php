<?php

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
    protected $signature = 'db:import {dbname} {sqlitefilepath} {--u|username=} {--p|password=} {--P|port=3306} {--t|type=mysql} {--host=localhost}';
    protected $description = 'Import an existing Flexisip database into FlexiAPI';
    private $_pagination = 1000;

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

        $capsule->addConnection([
            'driver'    => 'sqlite',
            'database'  => $this->argument('sqlitefilepath'),
        ], 'sqlite');

        $capsule->setAsGlobal();

        // Ensure that the target database is empty
        if (Account::count() > 0) {
            $this->error('An empty database is required to run the migration');
            return 1;
        }

        $accountsCount = Capsule::table('accounts')->count();

        if ($this->confirm($accountsCount . ' accounts will be migrated : Do you wish to continue?')) {
            // Accounts

            $this->info('Migrating the accounts');

            $pages = $accountsCount / $this->_pagination;
            $bar = $this->output->createProgressBar($pages);

            for ($page = 0; $page <= $pages; $page++) {
                $originAccounts = Capsule::table('accounts')
                                         ->take($this->_pagination)
                                         ->skip($page*$this->_pagination)
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

            $pages = Capsule::table('accounts')->count() / $this->_pagination;
            $bar = $this->output->createProgressBar($pages);

            for ($page = 0; $page <= $pages; $page++) {
                $originPasswords = Capsule::table('passwords')
                                          ->take($this->_pagination)
                                          ->skip($page*$this->_pagination)
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

            $pages = Capsule::table('aliases')->count() / $this->_pagination;
            $bar = $this->output->createProgressBar($pages);

            for ($page = 0; $page <= $pages; $page++) {
                $originAliases = Capsule::table('aliases')
                                          ->take($this->_pagination)
                                          ->skip($page*$this->_pagination)
                                          ->get()
                                          ->map(function ($element) {
                                            return (array)$element;
                                          })
                                          ->toArray();

                Alias::insert($originAliases);

                $bar->advance();
            }

            $bar->finish();

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

            $this->enableForeignKeyCheck();

            $this->newLine();
            $this->info('Databases migrated');
        }

        return 0;
    }
}
