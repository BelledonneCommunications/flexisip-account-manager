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

include_once __DIR__ . '/../misc/utilities.php';

include_once __DIR__ . '/../misc/results_values.php';

// args = [user, pwd, [domain], [algo]]
// /!\ This method must be used for tests purposes only /!\
function xmlrpc_get_confirmation_key($method, $args)
{
    $user = $args[0];
    $pwd = $args[1];
    $domain = get_domain($args[2]);
    $algo = get_algo($args[3]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_get_confirmation_key(" . $user . ", " . $domain . ", " . $algo . ")");

    if (!check_parameter($user)) {
        return MISSING_USERNAME_PARAM;
    } elseif (!ALLOW_TEST_ACCOUNTS) {
        Logger::getInstance()->error("Non test account unauthorized");
        return TEST_ACCOUNTS_DISABLED;
    } elseif ($algo == null) {
        return ALGO_NOT_SUPPORTED;
    }

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $user;
    $account->domain = $domain;

    if (!$account->getOne()) {
        return ACCOUNT_NOT_FOUND;
    }

    $password = new Password($db);
    $password->account_id = $account->id;
    $password->algorithm = $algo;

    if (!$password->getOne()) {
        return PASSWORD_NOT_FOUND;
    }

    if ($algo == CLEAR) {
        $hashed_password = $pwd;
    } else {
        $hashed_password = hash_password($user, $pwd, $domain, $algo);
    }

    if (!password_match($hashed_password, $password->password)
        && !password_match($pwd, $password->password)) { // This condition is specific for liblinphone tester....
        return PASSWORD_DOESNT_MATCH;
    }

    if ($account->confirmation_key == INVALID_CONFIRMATION_KEY) {
        // We have to generate a new one because
        $account->confirmation_key = uniqid();
        $account->update();
    }

    $key = $account->confirmation_key;
    Logger::getInstance()->debug("[XMLRPC] returning key = " . $key);
    return $key;
}

// args = [user, pwd, [domain], [algo]]
// /!\ This method must be used for tests purposes only /!\
function xmlrpc_delete_account($method, $args)
{
    $user = $args[0];
    $pwd = $args[1];
    $domain = get_domain($args[2]);
    $algo = get_algo($args[3]);

    Logger::getInstance()->message("[XMLRPC] xmlrpc_delete_account(" . $user . ", " . $domain . ", " . $algo . ")");

    if ($algo == null) {
        return ALGO_NOT_SUPPORTED;
    } elseif (!check_parameter($user)) {
        return MISSING_USERNAME_PARAM;
    } elseif (!ALLOW_TEST_ACCOUNTS) {
        return TEST_ACCOUNTS_DISABLED;
    }

    $database = new Database();
    $db = $database->getConnection();
    $account = new Account($db);
    $account->username = $user;
    $account->domain = $domain;

    if (!$account->getOne()) {
        return ACCOUNT_NOT_FOUND;
    }

    $password = new Password($db);
    $password->account_id = $account->id;
    $password->algorithm = $algo;

    if (!$password->getOne()) {
        return PASSWORD_NOT_FOUND;
    }

    if ($algo == CLEAR) {
        $hashed_password = $pwd;
    } else {
        $hashed_password = hash_password($user, $pwd, $domain, $algo);
    }
    if (!password_match($hashed_password, $password->password)
        && !password_match($pwd, $password->password)) { // This condition is specific for liblinphone tester....
        return PASSWORD_DOESNT_MATCH;
    }

    $alias = new Alias($db);
    $alias->account_id = $account->id;

    $account->delete();
    $password->delete();
    $alias->delete();

    return OK;
}

// /!\ This methods must be used for tests purposes only /!\
function xmlrpc_liblinphone_tester_register_methods($server)
{
    xmlrpc_server_register_method($server, 'get_confirmation_key', 'xmlrpc_get_confirmation_key');// args = [user, pwd, [domain], [algo]], return confirmation_key
    xmlrpc_server_register_method($server, 'delete_account', 'xmlrpc_delete_account');// args = [user, pwd, [domain], [algo]]
}
