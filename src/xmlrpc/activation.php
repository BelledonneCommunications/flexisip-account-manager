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
include_once __DIR__ . '/../objects/password.php';
include_once __DIR__ . '/../objects/alias.php';
include_once __DIR__ . '/../objects/user_info.php';

include_once __DIR__ . '/../misc/utilities.php';

include_once __DIR__ . '/accounts_email.php';
include_once __DIR__ . '/accounts_phone.php';

include_once __DIR__ . '/../misc/results_values.php';

function activate_email_account($user, $domain, $key, $algo) {
    $domain = get_domain($domain);
    $algo = get_algo($algo);
    Logger::getInstance()->message("[HTTP] activate_email_account(" . $user . ", " . $domain . ", " . $key . ", " . $algo . ")");

    if (!check_parameter($user)) {
        return MISSING_USERNAME_PARAM;
    } elseif ($algo == null) {
        return ALGO_NOT_SUPPORTED;
    }

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $user;
    $account->domain = $domain;

    if (!$account->getOne()) {
        Logger::getInstance()->error("[HTTP] Account not found");
        return ACCOUNT_NOT_FOUND;
    } elseif ($account->activated != "0") {
        Logger::getInstance()->warning("[HTTP] Account already activated");
        return ACCOUNT_ALREADY_ACTIVATED;
    }

    if (!is_key_matching($key, $account)) {
        Logger::getInstance()->error("[HTTP] Key doesn't match");
        return KEY_DOESNT_MATCH;
    }

    $account->activated = "1";
    $account->update();
    Logger::getInstance()->message("[HTTP] Account activated");
}

$user = $_GET["username"];
$domain = $_GET["domain"];
$key = $_GET["confirmation_key"];
$algo = $_GET["algo"];
activate_email_account($user, $domain, $key, $algo);

?>