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

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../../database/database.php';
include_once __DIR__ . '/../../objects/account.php';
include_once __DIR__ . '/../../objects/password.php';
include_once __DIR__ . '/../../objects/alias.php';
include_once __DIR__ . '/../../misc/utilities.php';

$database = new Database();
$db = $database->getConnection();

$account = new Account($db);

$data = json_decode(file_get_contents("php://input"));
if ((!empty($data->username) || !empty($data->phone)) && (!empty($data->password) || GENERATE_PASSWORD_ENABLED) && (!empty($data->phone) || !empty($data->email))) {
    $account->username = empty($data->username) ? $data->phone : $data->username;
    $account->domain = empty($data->domain) ? SIP_DOMAIN : $data->domain;
    $account->email = empty($data->email) ? null : $data->email;
    $account->activated = AUTO_ACTIVATE_ACCOUNT ? '1' : '0';
    $account->confirmation_key = empty($data->phone) ? uniqid() : generate_4_digits_code();
    $account->ip_address = getIp();
    $account->user_agent = empty($data->user_agent) ? $_SERVER['HTTP_USER_AGENT'] : $data->user_agent;
    $account->expire_time = empty($data->expire_time) ? null : $data->expire_time;

    if (!$account->getOne()) {
        if ($account->create()) {
            $password = new Password($db);
            $password->account_id = $account->id;
            $password->algorithm = empty($data->algorithm) ? 'MD5' : $data->algorithm;

            if (GENERATE_PASSWORD_ENABLED) {
                $password->password = hash_password($account->username, generate_password(), $account->domain, $password->algorithm);
            } else {
                $password->password = empty($data->password)
                    ? hash_password($account->username, generate_password(), $account->domain, $password->algorithm)
                    : $data->password;
            }
            if (!$password->create()) {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create password for account."));
                return;
            }

            if (!empty($data->phone) && $data->phone != $account->username) {
                $alias = new Alias($db);
                $alias->account_id = $account->id;
                $alias->alias = $data->phone;
                $alias->domain = $account->domain;
                if (!$alias->create()) {
                    http_response_code(503);
                    echo json_encode(array("message" => "Unable to create alias for account."));
                    return;
                }
            }

            http_response_code(201);
            echo json_encode(array("message" => "Account was created."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create account."));
        }
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Account already exists"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create account, data is incomplete."));
}
