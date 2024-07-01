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

namespace App\Libraries;

use App\Device;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use stdClass;

class FlexisipConnector
{
    public function getDevices(string $from)
    {
        $devices = collect();

        try {
            $content = Redis::hgetall('fs:' . $from);

            foreach ($content as $key => $contact) {
                $device = new Device;
                $device->fromRedisContact($contact);
                $devices->push($device);
            }
        } catch (\Throwable $th) {
            Log::error('Redis server issue: ' . $th->getMessage());
        }

        return $devices->keyBy('uuid');
    }

    public function deleteDevice(string $from, string $uuid)
    {
        try {
            Redis::hdel('fs:' . $from, '"<' . $uuid . '>"');
        } catch (\Throwable $th) {
            Log::error('Redis server issue: ' . $th->getMessage());
        }
    }
}
