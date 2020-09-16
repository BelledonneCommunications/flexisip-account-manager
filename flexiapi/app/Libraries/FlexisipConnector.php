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

class FlexisipConnector
{
    private $_socket;

    public function __construct()
    {
        $pid = file_get_contents(config('app.flexisip_proxy_pid'));
        $this->_socket = stream_socket_client('unix:///tmp/flexisip-proxy-'.$pid, $errno, $errstr);
    }

    public function __destruct()
    {
        fclose($this->_socket);
    }

    public function getDevices(string $from)
    {
        $content = $this->request('REGISTRAR_GET', [
            'sip:'.$from
        ]);
        $devices = collect();

        if (isset($content->contacts)) {
            foreach ($content->contacts as $contact) {
                $device = new Device;
                $device->fromContact($contact);
                $devices->push($device);
            }
        }

        return $devices;
    }

    public function deleteDevice(string $from, string $uuid)
    {
        $content = $this->request('REGISTRAR_DELETE', [
            'sip:'.$from,
            '"<'.$uuid.'>"',
        ]);
    }

    private function request(string $command, array $parameters)
    {
        fwrite($this->_socket, $command.' '.\implode(' ', $parameters));
        return json_decode(fread($this->_socket, 8192));
    }
}