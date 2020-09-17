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
use Ovh\Api;

class OvhSMS
{
    private $_api;
    private $_smsService;

    public function __construct()
    {
        $this->_api = new Api(
            config('ovh.app_key'),
            config('ovh.app_secret'),
            config('ovh.app_endpoint'),
            config('ovh.app_consumer_key')
        );

        $smsServices = $this->_api->get('/sms/');
        if (!empty($smsServices)) $this->_smsService = $smsServices[0];
    }

    public function send(string $to, string $message)
    {
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

        $resultPostJob = $this->_api->post('/sms/'. $this->_smsService . '/jobs', $content);
        // One credit removed

        $smsJobs = $this->_api->get('/sms/'. $this->_smsService . '/jobs');
    }
}