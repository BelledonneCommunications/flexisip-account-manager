<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\AccountType;

class AccountTypeSeeder extends Seeder
{
    public function run()
    {
        AccountType::create(['key' => 'phone']);
        AccountType::create(['key' => 'door']);
    }
}
