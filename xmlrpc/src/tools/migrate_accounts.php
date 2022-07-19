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
include_once __DIR__ . '/../misc/utilities.php';

$database = new Database();
$db = $database->getConnection();
$old_db = null;

try {
    $old_db = new PDO("mysql:host=" . DB_HOST . ";dbname=belledonne_proxy", DB_USER, DB_PASSWORD);
    $old_db->exec("set names utf8");
} catch (PDOException $exception) {
    Logger::getInstance()->error("Connection error: " . $exception->getMessage());
    return;
}

Logger::getInstance()->message("Ready to migrate");

$start_time = time();

/* **************************************************** */

Logger::getInstance()->message("Starting accounts migration");

$query = "SELECT ac.id, ac.login, ac.password, ac.activated, ac.email, ac.confirmation_key, ac.ip_address, ac.date_last_update, ac.user_agent, ac.firstname, ac.name, ac.gender, ac.subscribe, al.alias FROM "
    . ACCOUNTS_DB_TABLE . " ac LEFT JOIN " . ALIAS_DB_TABLE . " al ON ac.id = al.account_id";
$old_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$old_db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false); // For large sets this is mandatory
$stmt = $old_db->prepare($query);
$stmt->execute();

$accounts_to_migrate_count = 0;
$account_created_count = 0;
$password_created_count = 0;
$alias_created_count = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $accounts_to_migrate_count += 1;
    extract($row);

    $account = new Account($db);
    $account->username = $login;
    $account->domain = SIP_DOMAIN;
    $account->email = $email;
    $account->activated = $activated;
    $account->confirmation_key = $confirmation_key;
    $account->ip_address = $ip_address;
    $account->user_agent = $user_agent;
    $account->creation_time = $date_last_update;
    $account->expire_time = null;

    if ($account->getOne()) {
        // If an account as multiple aliases we will go here
        if (!empty($alias)) {
            $al = new Alias($db);
            $al->account_id = $account->id;
            $al->alias = $alias;
            $al->domain = $account->domain;

            if (!$al->create()) {
                Logger::getInstance()->error("Failed to create alias !");
            } else {
                $alias_created_count += 1;
            }
        }
    } else {
        if ($account->create()) {
            $account_created_count += 1;

            $user_info = new UserInfo($db);
            $user_info->account_id = $account->id;
            $user_info->firstname = $firstname;
            $user_info->lastname = $name;
            $user_info->gender = $gender;
            $user_info->subscribe = $subscribe;
            if (!$user_info->create()) {
                Logger::getInstance()->error("Failed to create user_info !");
            }

            $pwd = new Password($db);
            $pwd->account_id = $account->id;
            $pwd->algorithm = 'MD5';
            $pwd->password = $password;

            if (!$pwd->create()) {
                Logger::getInstance()->error("Failed to create password !");
            } else {
                $password_created_count += 1;
            }

            if (!empty($alias)) {
                $al = new Alias($db);
                $al->account_id = $account->id;
                $al->alias = $alias;
                $al->domain = $account->domain;

                if (!$al->create()) {
                    Logger::getInstance()->error("Failed to create alias !");
                } else {
                    $alias_created_count += 1;
                }
            }
        } else {
            Logger::getInstance()->error("Failed to create account !");
        }
    }
}

Logger::getInstance()->message("Accounts migration done");
Logger::getInstance()->message($accounts_to_migrate_count . " were to migrate, " . $account_created_count . " were succesfully created including "
    . $password_created_count . " passwords and " . $alias_created_count . " aliases");

/* **************************************************** */

Logger::getInstance()->message("Starting SMS migration");

$all_sms = new SMS($old_db);
$stmt = $all_sms->getAll();

$sms_to_migrate_count = 0;
$sms_created_count = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sms_to_migrate_count += 1;
    extract($row);

    $sms = new SMS($db);
    $sms->phone = $phone;
    $sms->last_sms = $last_sms;
    $sms->count = $count;

    if (!$sms->create()) {
        Logger::getInstance()->error("Failed to create sms !");
    } else {
        $sms_created_count += 1;
    }
}

Logger::getInstance()->message("SMS migration done");
Logger::getInstance()->message($sms_to_migrate_count . " were to migrate, " . $sms_created_count . " were succesfully created");

/* **************************************************** */

Logger::getInstance()->message("Starting devices migration");

$all_devices = new Device($old_db);
$stmt = $all_devices->getAll();

$devices_to_migrate_count = 0;
$devices_created_count = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $devices_to_migrate_count += 1;
    extract($row);

    $device = new Device($db);
    $device->manufacturer = $manufacturer;
    $device->model = $model;
    $device->status = $status;
    $device->delay = $delay;
    $device->hardware_echo_canceller = $hardware_echo_canceller;

    if (!$device->create()) {
        Logger::getInstance()->error("Failed to create device !");
    } else {
        $devices_created_count += 1;
    }
}

Logger::getInstance()->message("Devices migration done");
Logger::getInstance()->message($devices_to_migrate_count . " were to migrate, " . $devices_created_count . " were succesfully created");

/* **************************************************** */

$end_time = time();

$time_diff = $end_time - $start_time;
Logger::getInstance()->message("Migration took " . time_elapsed_as_string($time_diff));

?>