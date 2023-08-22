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

use Ovh\Api;
use Illuminate\Support\Facades\Log;

class OvhSMS
{
    private $api;
    private $smsService;

    public function __construct()
    {
        if (empty(config('ovh.app_key'))) {
            Log::error('OVH SMS API not configured');
        }

        $this->api = new Api(
            config('ovh.app_key'),
            config('ovh.app_secret'),
            config('ovh.app_endpoint'),
            config('ovh.app_consumer_key')
        );

        try {
            $smsServices = $this->api->get('/sms/');

            if (!empty($smsServices)) {
                $this->smsService = $smsServices[0];
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::channel('events')->info('OVH SMS API unreachable, check the errors log');
            Log::error('OVH SMS API not reachable: ' . $e->getMessage());
        }
    }

    public function send(string $to, string $message)
    {
        if (!$this->smsService) {
            Log::error('OVH SMS API not configured');
            return;
        }

        $content = (object) [
            'charset'           => 'UTF-8',
            'class'             => 'phoneDisplay',
            'coding'            => '7bit',
            'message'           => $message,
            'noStopClause'      => true,
            'priority'          => 'high',
            'receivers'         => [ $to ],
            'sender'            => config('ovh.app_sender'),
            'senderForResponse' => false,
            'validityPeriod'    => 2880
        ];

        Log::channel('events')->info('OVH SMS sending', ['to' => $to, 'message' => $message]);

        try {
            $this->api->post('/sms/'. $this->smsService . '/jobs', $content);
            // One credit removed

            $this->api->get('/sms/'. $this->smsService . '/jobs');
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::channel('events')->info('OVH SMS not sent, check the errors log');
            Log::error('OVH SMS not sent: ' . $e->getMessage());
        }
    }
}
