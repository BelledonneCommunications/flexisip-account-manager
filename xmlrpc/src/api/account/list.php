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

$database = new Database();
$db = $database->getConnection();

$account = new Account($db);

$stmt = $account->getAll();
$num = $stmt->rowCount();

if ($num > 0) {
    $accounts = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $account_item = array(
            "id" => $id,
            "username" => $username,
            "domain" => $domain,
            "activated" => $activated,
            "alias" => $alias
        );
        array_push($accounts, $account_item);
    }

    http_response_code(200);
    echo json_encode($accounts);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No account found"));
}
