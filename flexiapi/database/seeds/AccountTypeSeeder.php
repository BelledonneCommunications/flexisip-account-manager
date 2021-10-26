<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\AccountType;

class AccountTypeSeeder extends Seeder
{
    public function run()
    {
        AccountType::create(['key' => 'device_audio_intercom']);
        AccountType::create(['key' => 'device_video_intercom']);
        AccountType::create(['key' => 'device_security_camera']);
        AccountType::create(['key' => 'device_internal_unit']);
    }
}
