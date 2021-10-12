<?php

use Database\Seeders\AccountTypeSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(AccountTypeSeeder::class);
    }
}
