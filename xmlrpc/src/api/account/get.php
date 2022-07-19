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

$database = new Database();
$db = $database->getConnection();

$account = new Account($db);
$account->id = isset($_GET['id']) ? $_GET['id'] : null;
$account->username = isset($_GET['username']) ? $_GET['username'] : null;
$account->domain = isset($_GET['domain']) ? $_GET['domain'] : null;

$account->getOne();
if (!empty($account->id) && !empty($account->username) && !empty($account->domain)) {
    $account_item = array(
        "id" => $account->id,
        "username" => $account->username,
        "domain" => $account->domain,
        "activated" => $account->activated,
        "alias" => $account->alias,
        "passwords" => array()
    );

    $pwd = new Password($db);
    $pwd->account_id = $account->id;

    $stmt = $pwd->getAll();
    $num = $stmt->rowCount();
    if ($num > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $password_item = array(
                "id" => $id,
                "password" => $password,
                "algorithm" => $algorithm
            );
            $account_item["passwords"][$algorithm] = $password_item;
        }
    }

    http_response_code(200);
    echo json_encode($account_item);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Account doesn't exist"));
}
