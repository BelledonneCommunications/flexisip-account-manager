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

include_once __DIR__ . '/../database/database.php';
include_once __DIR__ . '/../objects/device.php';
include_once __DIR__ . '/../misc/results_values.php';

// args = [manufacturer, model, status, delay, hasHEC]
function xmlrpc_add_ec_calibration_result($method, $args)
{
    $manufacturer = $args[0];
    $model = $args[1];
    $status = $args[2];
    $delay = $args[3];

    $hasHEC = 0;
    if (count($args) == 5) {
        $hasHEC = (int)$args[4];
    }

    $database = new Database();
    $db = $database->getConnection();

    $device = new Device($db);
    $device->manufacturer = $manufacturer;
    $device->model = $model;
    $device->status = $status;
    $device->delay = $delay;
    $device->hardware_echo_canceller = $hasHEC;
    $device->create();

    return OK;
}

function xmlrpc_devices_register_methods($server)
{
    xmlrpc_server_register_method($server, 'add_ec_calibration_result', 'xmlrpc_add_ec_calibration_result');// args = [manufacturer, model, status, delay, hasHEC]
}
