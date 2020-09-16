#!/usr/bin/env php

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
include_once __DIR__ . '/../objects/account.php';
include_once __DIR__ . '/../objects/alias.php';
include_once __DIR__ . '/../objects/device.php';
include_once __DIR__ . '/../objects/password.php';
include_once __DIR__ . '/../objects/sms.php';
include_once __DIR__ . '/../objects/user_info.php';

$database = new Database();
$db = $database->getConnection();

$account = new Account($db);
if (!$account->createTable()) {
    Logger::getInstance()->error("Couldn't create account table");
}

$alias = new Alias($db);
if (!$alias->createTable()) {
    Logger::getInstance()->error("Couldn't create alias table");
}

$device = new Device($db);
if (!$device->createTable()) {
    Logger::getInstance()->error("Couldn't create device table");
}

$password = new Password($db);
if (!$password->createTable()) {
    Logger::getInstance()->error("Couldn't create password table");
}

$sms = new SMS($db);
if (!$sms->createTable()) {
    Logger::getInstance()->error("Couldn't create sms table");
}

$user_info = new UserInfo($db);
if (!$user_info->createTable()) {
    Logger::getInstance()->error("Couldn't create user_info table");
}
