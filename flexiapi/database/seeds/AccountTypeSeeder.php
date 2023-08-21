<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\AccountType;

class AccountTypeSeeder extends Seeder
{
    public function run()
    {
        AccountType::updateOrCreate(['key' => 'device_audio_intercom']);
        AccountType::updateOrCreate(['key' => 'device_video_intercom']);
        AccountType::updateOrCreate(['key' => 'device_security_camera']);
        AccountType::updateOrCreate(['key' => 'device_internal_unit']);
    }
}
