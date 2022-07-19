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

$logger = Logger::getInstance();

$database = new Database();
$db = $database->getConnection();

$account = new Account($db);

$data = json_decode(file_get_contents("php://input"));
if (!empty($data->id) || !empty($data->username)) {
    if (!empty($data->id)) {
        $account->id = $data->id;
    } elseif (!empty($data->username)) {
        $account->username = $data->username;
        if (!empty($data->domain)) {
            $account->domain = $data->domain;
        }
    }

    if ($account->getOne()) {
        $password = new Password($db);
        $password->account_id = $account->id;

        $alias = new Alias($db);
        $alias->account_id = $account->id;

        if ($account->delete()) {
            if (!$password->delete()) {
                $logger->error("Failed to delete password(s) for account id " . $password->account_id);
            }

            if ($alias->getOne()) {
                if (!$alias->delete()) {
                    $logger->error("Failed to delete alias for account id " . $alias->account_id);
                }
            }

            http_response_code(200);
            echo json_encode(array("message" => "Account was deleted."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to delete account."));
        }
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Account doesn't exist"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to delete account, data is incomplete."));
}
